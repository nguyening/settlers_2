<?php
class GameBuildTest extends PHPUnit_Framework_TestCase {
	protected $game;
	protected $map;
	protected $map_reflection;
	protected $game_reflection;
	protected $hexes;
	protected $player;

	public function setUp()
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$game = new \Settlers\Game(array(
			'room_size' => 4,
			'map_size' => 2
		));
		$game->setupMap(2);
		$game->shuffleAssignments();
		
		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$this->game = $game;
		$this->map = $map;
		$this->map_reflection = $map_reflection;
		$this->game_reflection = $game_reflection;
		$this->hexes = $hexes;
		$this->player = $player;
	}

	public function testBuildCityInvalid()
	{
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$this->assertFalse($this->game->canBuildPiece(
			$this->player, $edge, \Settlers\Constants::BUILD_CITY
		));
	}

	public function testBuildCity()
	{
		$game = $this->game;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);

		$game->buildPiece($this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT);
		$this->assertTrue($game->canBuildPiece($this->player, $vertex, \Settlers\Constants::BUILD_CITY));

		$game->buildPiece($this->player, $vertex, \Settlers\Constants::BUILD_CITY);
		$this->assertNotNull($vertex->getPiece());
		$this->assertEquals(\Settlers\Constants::BUILD_CITY, $vertex->getPiece()->getType());
	}

	public function dataSettlementConnection()
	{
		return array(
			array(array(0,0,0), array(0,0,0)),
			array(array(0,0,0), array(0,-1,3)),
			array(array(0,0,0), array(0,0,5)),
			array(array(0,0,0), array(0,-1,4)),

			array(array(0,-2,0), array(0,-2,0)),
			array(array(0,-2,0), array(0,-2,5)),

			array(array(2,-2,0), array(2,-2,0)),
			array(array(2,-2,0), array(2,-2,5)),
			array(array(2,-2,0), array(1,-2,2)),
			array(array(2,-2,0), array(1,-2,1))
		);
	}

	/**
	 * @dataProvider dataSettlementConnection
	 */
	public function testSettlementConnection($v_coords, $e_coords)
	{
		$game = $this->game;
		$player = $this->player;
		$hexes = $this->hexes;

		$vertex = $hexes[$v_coords[1]][$v_coords[0]]->getVertex($v_coords[2]);
		$edge = $hexes[$e_coords[1]][$e_coords[0]]->getEdge($e_coords[2]);

		$this->assertFalse($game->canBuildPiece($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT));

		$game->buildPiece($player, $edge, \Settlers\Constants::BUILD_ROAD);
		$this->assertTrue($game->canBuildPiece($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT));

	}

	public function testBuildDistance()
	{
		$game = $this->game;
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);

		$getAdjacentVertices = $this->map_reflection->getMethod('getAdjacentVertices');
		$getAdjacentVertices->setAccessible(true);
		
		$getAdjacentEdges = $this->map_reflection->getMethod('getAdjacentEdges');
		$getAdjacentEdges->setAccessible(true);

		$isAdjacentVertex = $this->map_reflection->getMethod('isAdjacentVertex');
		$isAdjacentVertex->setAccessible(true);

		$neighbors = $getAdjacentVertices->invokeArgs($map, array($vertex));

		$incidentEdges = array(
			$hexes[0][0]->getEdge(0),
			$hexes[0][0]->getEdge(5),
			$hexes[0][-1]->getEdge(1)
		);

		$game->buildPiece($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT);
		foreach($neighbors as $idx => $neighbor) {
			$this->assertFalse($game->canBuildPiece($player, $neighbor, \Settlers\Constants::BUILD_SETTLEMENT));
		}

		for($i = 0; $i < 6; $i++) {
			$this->assertFalse($game->canBuildPiece(
				$player, $hexes[2][0]->getVertex($i), \Settlers\Constants::BUILD_SETTLEMENT
			));
		}

		// Lay out length-2 roads to try and build settlements
		foreach($incidentEdges as $idx => $edge) {
			$game->buildPiece($player, $edge, \Settlers\Constants::BUILD_ROAD);

			foreach($getAdjacentEdges->invokeArgs($map, array($edge))
				as $idx => $neighbor) {
				// We don't want to accidently go back to length-1 roads
				if(in_array($neighbor, $incidentEdges, true)) continue;

				$game->buildPiece($player, $neighbor, \Settlers\Constants::BUILD_ROAD);

				$v1 = $neighbor->getVertex(0);
				$v2 = $neighbor->getVertex(1);

				if(!$isAdjacentVertex->invokeArgs($map, array($v1, $vertex))) {
					$v = $v1;
				}
				else {
					$v = $v2;
				}

				$this->assertTrue($game->canBuildPiece($player, $v, \Settlers\Constants::BUILD_SETTLEMENT));
			}
		}
	}

	public function testRoadGrowth()
	{
		$game = $this->game;
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;

		$vertex = $hexes[0][0]->getVertex(0);

		$getAdjacentVertices = $this->map_reflection->getMethod('getAdjacentVertices');
		$getAdjacentVertices->setAccessible(true);

		$getAdjacentEdges = $this->map_reflection->getMethod('getAdjacentEdges');
		$getAdjacentEdges->setAccessible(true);

		$neighbors = $getAdjacentVertices->invokeArgs($this->map, array($vertex));

		$incidentEdges = array(
			$hexes[0][0]->getEdge(0),
			$hexes[0][0]->getEdge(5),
			$hexes[0][-1]->getEdge(1)
		);

		$game->buildPiece($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT);

		// Edges in some random corner of the map
		for($i = 0; $i < 6; $i++) {
			$this->assertFalse($game->canBuildPiece(
				$player, $hexes[1][1]->getEdge($i), \Settlers\Constants::BUILD_ROAD
			));
		}

		foreach($incidentEdges as $idx => $edge) {
			$this->assertTrue($game->canBuildPiece($player, $edge, \Settlers\Constants::BUILD_ROAD));
			$game->buildPiece($player, $edge, \Settlers\Constants::BUILD_ROAD);

			foreach($getAdjacentEdges->invokeArgs($map, array($edge))
				as $idx => $neighbor) {
				// We don't want to accidently go back to length-1 roads
				if(in_array($neighbor, $incidentEdges, true)) continue;
			
				$this->assertTrue($game->canBuildPiece(
					$player, $neighbor, \Settlers\Constants::BUILD_ROAD
				));
			}
		}
	}
}