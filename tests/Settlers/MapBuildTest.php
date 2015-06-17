<?php
class MapBuildTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;
	protected $hexes;
	protected $player;
	protected $placePiece;
	protected $canBuildPiece;

	public function setUp()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 2
		));
		$map_reflection = new ReflectionClass('\Settlers\Map');
		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$placePiece = $map_reflection->getMethod('placePiece');
		$placePiece->setAccessible(true);

		$canBuildPiece = $map_reflection->getMethod('canBuildPiece');
		$canBuildPiece->setAccessible(true);

		$this->map = $map;
		$this->map_reflection = $map_reflection;
		$this->hexes = $hexes;
		$this->player = $player;
		$this->placePiece = $placePiece;
		$this->canBuildPiece = $canBuildPiece;
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	3
	 */
	public function testPlaceSettlementInvalid()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$this->placePiece->invokeArgs($map, 
			array($this->player, $edge, \Settlers\Constants::BUILD_SETTLEMENT)
		);
	}

	public function testPlaceRoad()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$this->placePiece->invokeArgs($map, 
			array($this->player, $edge, \Settlers\Constants::BUILD_ROAD)
		);

		$this->assertNotNull($edge->getPiece());
		$this->assertEquals(\Settlers\Constants::BUILD_ROAD, $edge->getPiece()->getType());
	}

	public function testPlaceSettlement()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);

		$this->placePiece->invokeArgs($map, 
			array($this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);

		$this->assertNotNull($vertex->getPiece());
		$this->assertEquals(\Settlers\Constants::BUILD_SETTLEMENT, $vertex->getPiece()->getType());
	}

	public function testIsVertexOccupied()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);
		
		$isVertexOccupied = $this->map_reflection->getMethod('isVertexOccupied');
		$isVertexOccupied->setAccessible(true);

		$this->assertFalse($isVertexOccupied->invokeArgs($map, array($vertex)));

		$this->placePiece->invokeArgs($map, 
			array($this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);

		$this->assertTrue($isVertexOccupied->invokeArgs($map, array($vertex)));
	}

	public function testIsEdgeOccupied()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);
		
		$isEdgeOccupied = $this->map_reflection->getMethod('isEdgeOccupied');
		$isEdgeOccupied->setAccessible(true);

		$this->assertFalse($isEdgeOccupied->invokeArgs($map, array($edge)));

		$this->placePiece->invokeArgs($map, 
			array($this->player, $edge, \Settlers\Constants::BUILD_ROAD)
		);

		$this->assertTrue($isEdgeOccupied->invokeArgs($map, array($edge)));
	}

	public function testIsVertexOccupiedByPlayer()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		
		$isVertexOccupiedByPlayer = $this->map_reflection->getMethod('isVertexOccupiedByPlayer');
		$isVertexOccupiedByPlayer->setAccessible(true);

		$this->assertFalse($isVertexOccupiedByPlayer->invokeArgs($map, array($vertex, $this->player)));
		$this->assertFalse($isVertexOccupiedByPlayer->invokeArgs($map, array($vertex, $p2)));

		$this->placePiece->invokeArgs($map, 
			array($p2, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);

		$this->assertFalse($isVertexOccupiedByPlayer->invokeArgs($map, array($vertex, $this->player)));
		$this->assertTrue($isVertexOccupiedByPlayer->invokeArgs($map, array($vertex, $p2)));
	}

	public function testIsEdgeOccupiedByPlayer()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		
		$isEdgeOccupiedByPlayer = $this->map_reflection->getMethod('isEdgeOccupiedByPlayer');
		$isEdgeOccupiedByPlayer->setAccessible(true);

		$this->assertFalse($isEdgeOccupiedByPlayer->invokeArgs($map, array($edge, $this->player)));
		$this->assertFalse($isEdgeOccupiedByPlayer->invokeArgs($map, array($edge, $p2)));

		$this->placePiece->invokeArgs($map, 
			array($p2, $edge, \Settlers\Constants::BUILD_ROAD)
		);

		$this->assertFalse($isEdgeOccupiedByPlayer->invokeArgs($map, array($edge, $this->player)));
		$this->assertTrue($isEdgeOccupiedByPlayer->invokeArgs($map, array($edge, $p2)));
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	3
	 */
	public function testBuildCityInvalid()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$map->buildPiece($this->player, $edge, \Settlers\Constants::BUILD_CITY);
	}

	public function testBuildCity()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);
		$canBuildPiece = $this->canBuildPiece;

		$this->placePiece->invokeArgs($map, 
			array($this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);
		$this->assertTrue($canBuildPiece->invokeArgs($map, 
			array($this->player, $vertex, \Settlers\Constants::BUILD_CITY))
		);
		$map->buildPiece($this->player, $vertex, \Settlers\Constants::BUILD_CITY);

		$this->assertNotNull($vertex->getPiece());
		$this->assertEquals(\Settlers\Constants::BUILD_CITY, $vertex->getPiece()->getType());
	}

	public function testSettlementConnection()
	{
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$canBuildPiece = $this->canBuildPiece;

		$vertex = $hexes[0][0]->getVertex(0);
		$edge = $hexes[0][0]->getEdge(0);

		$this->assertFalse($canBuildPiece->invokeArgs($map,
			array($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT))
		);

		$this->placePiece->invokeArgs($map,
			array($player, $edge, \Settlers\Constants::BUILD_ROAD)
		);

		$this->assertTrue($canBuildPiece->invokeArgs($map,
			array($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT))
		);
	}

	public function testBuildDistance()
	{
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$canBuildPiece = $this->canBuildPiece;

		$vertex = $hexes[0][0]->getVertex(0);

		$getAdjacentVertices = $this->map_reflection->getMethod('getAdjacentVertices');
		$getAdjacentVertices->setAccessible(true);
		
		$getAdjacentEdges = $this->map_reflection->getMethod('getAdjacentEdges');
		$getAdjacentEdges->setAccessible(true);

		$isAdjacentVertex = $this->map_reflection->getMethod('isAdjacentVertex');
		$isAdjacentVertex->setAccessible(true);

		$neighbors = $getAdjacentVertices->invokeArgs($this->map, array($vertex));

		$incidentEdges = array(
			$hexes[0][0]->getEdge(0),
			$hexes[0][0]->getEdge(5),
			$hexes[0][-1]->getEdge(1)
		);

		$this->placePiece->invokeArgs($map,
			array($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);

		foreach($neighbors as $idx => $neighbor) {
			$this->assertFalse($canBuildPiece->invokeArgs($map, 
				array($player, $neighbor, \Settlers\Constants::BUILD_SETTLEMENT))
			);
		}

		for($i = 0; $i < 6; $i++) {
			$this->assertFalse($canBuildPiece->invokeArgs($map,
				array($player, $hexes[2][0]->getVertex($i), \Settlers\Constants::BUILD_SETTLEMENT))
			);
		}

		// Lay out length-2 roads to try and build settlements
		foreach($incidentEdges as $idx => $edge) {
			$this->placePiece->invokeArgs($map,
				array($player, $edge, \Settlers\Constants::BUILD_ROAD)
			);

			foreach($getAdjacentEdges->invokeArgs($map, array($edge))
				as $idx => $neighbor) {
				// We don't want to accidently go back to length-1 roads
				if(in_array($neighbor, $incidentEdges, true)) continue;

				$this->placePiece->invokeArgs($map,
					array($player, $neighbor, \Settlers\Constants::BUILD_ROAD)
				);

				$v1 = $neighbor->getVertex(0);
				$v2 = $neighbor->getVertex(1);

				if(!$isAdjacentVertex->invokeArgs($map, array($v1, $vertex))) {
					$v = $v1;
				}
				else {
					$v = $v2;
				}

				$this->assertTrue($canBuildPiece->invokeArgs($map,
					array($player, $v, \Settlers\Constants::BUILD_SETTLEMENT))
				);
			}
		}
	}

	public function testRoadBranching()
	{
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$canBuildPiece = $this->canBuildPiece;

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

		$this->placePiece->invokeArgs($map,
			array($player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT)
		);

		// Edges in some random corner of the map
		for($i = 0; $i < 6; $i++) {
			$this->assertFalse($canBuildPiece->invokeArgs($map,
				array($player, $hexes[1][1]->getEdge($i), \Settlers\Constants::BUILD_ROAD))
			);
		}

		foreach($incidentEdges as $idx => $edge) {
			$this->assertTrue($canBuildPiece->invokeArgs($map,
				array($player, $edge, \Settlers\Constants::BUILD_ROAD))
			);

			$this->placePiece->invokeArgs($map,
				array($player, $edge, \Settlers\Constants::BUILD_ROAD)
			);

			foreach($getAdjacentEdges->invokeArgs($map, array($edge))
				as $idx => $neighbor) {
				// We don't want to accidently go back to length-1 roads
				if(in_array($neighbor, $incidentEdges, true)) continue;
			
				$this->assertTrue($canBuildPiece->invokeArgs($map,
					array($player, $neighbor, \Settlers\Constants::BUILD_ROAD))
				);
			}
		}
	}

}