<?php
class MapPlacementTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;
	protected $hexes;
	protected $player;

	public function setUp()
	{
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$map = new \Settlers\Map(array(
			'map_size' => 2
		));

		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$this->map = $map;
		$this->map_reflection = $map_reflection;
		$this->hexes = $hexes;
		$this->player = $player;
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

		$map->placePiece( 
			$this->player, $edge, \Settlers\Constants::BUILD_SETTLEMENT
		);
	}

	public function testPlaceRoad()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$map->placePiece( 
			$this->player, $edge, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertNotNull($edge->getPiece());
		$this->assertEquals(\Settlers\Constants::BUILD_ROAD, $edge->getPiece()->getType());
	}

	public function testPlaceSettlement()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);

		$map->placePiece( 
			$this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT
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

		$map->placePiece( 
			$this->player, $vertex, \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertTrue($map->isVertexOccupied($vertex));
	}

	public function testIsEdgeOccupied()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);

		$this->assertFalse($map->isEdgeOccupied($edge));

		$map->placePiece( 
			$this->player, $edge, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertTrue($map->isEdgeOccupied($edge));
	}

	public function testIsVertexOccupiedByPlayer()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();


		$this->assertFalse($map->isVertexOccupiedByPlayer($vertex, $this->player));
		$this->assertFalse($map->isVertexOccupiedByPlayer($vertex, $p2));

		$map->placePiece( 
			$p2, $vertex, \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertFalse($map->isVertexOccupiedByPlayer($vertex, $this->player));
		$this->assertTrue($map->isVertexOccupiedByPlayer($vertex, $p2));
	}

	public function testIsEdgeOccupiedByPlayer()
	{
		$map = $this->map;
		$hexes = $this->hexes;
		$edge = $hexes[0][0]->getEdge(0);
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$this->assertFalse($map->isEdgeOccupiedByPlayer($edge, $this->player));
		$this->assertFalse($map->isEdgeOccupiedByPlayer($edge, $p2));

		$map->placePiece( 
			$p2, $edge, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertFalse($map->isEdgeOccupiedByPlayer($edge, $this->player));
		$this->assertTrue($map->isEdgeOccupiedByPlayer($edge, $p2));
	}	

	public function testAdjacentOccupiedVertex()
	{
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$getAdjacentVertices = $this->map_reflection->getMethod('getAdjacentVertices');
		$getAdjacentVertices->setAccessible(true);

		$v1 = $hexes[0][0]->getVertex(0);
		$v2 = $hexes[0][0]->getVertex(2);

		$map->placePiece( 
			$this->player, $v1, \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertFalse($map->isAdjacentVerticesOccupied($v1));
		foreach($getAdjacentVertices->invokeArgs($this->map, array($v1)) as $idx => $neighbor) {
			$this->assertTrue($map->isAdjacentVerticesOccupied($neighbor));
		}
	}

	public function testAdjacentEdgesOccupiedByPlayer() {
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$e1 = $hexes[0][0]->getEdge(0);
		$e2 = $hexes[0][0]->getEdge(1);
		$map->placePiece( 
			$player, $e1, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertFalse($map->isAdjacentEdgesOccupiedByPlayer($e1, $player));
		$this->assertFalse($map->isAdjacentEdgesOccupiedByPlayer($e1, $p2));
		$this->assertTrue($map->isAdjacentEdgesOccupiedByPlayer($e2, $player));
		$this->assertFalse($map->isAdjacentEdgesOccupiedByPlayer($e2, $p2));

		$map->placePiece( 
			$p2, $e2, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertFalse($map->isAdjacentEdgesOccupiedByPlayer($e1, $player));
		$this->assertTrue($map->isAdjacentEdgesOccupiedByPlayer($e1, $p2));
		$this->assertTrue($map->isAdjacentEdgesOccupiedByPlayer($e2, $player));
		$this->assertFalse($map->isAdjacentEdgesOccupiedByPlayer($e2, $p2));

		$e3 = $hexes[-1][0]->getEdge(2);
		$this->assertTrue($map->isAdjacentEdgesOccupiedByPlayer($e3, $player));
		$this->assertTrue($map->isAdjacentEdgesOccupiedByPlayer($e3, $p2));
	}

	public function testEndpointsOccupiedByPlayer() {
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$edge = $hexes[0][0]->getEdge(0);
		$v1 = $hexes[0][0]->getVertex(0);
		$v2 = $hexes[0][0]->getVertex(1);

		$this->assertFalse($map->isEndpointsOccupiedByPlayer($edge, $player));
		$this->assertFalse($map->isEndpointsOccupiedByPlayer($edge, $p2));

		$map->placePiece( 
			$player, $v1, \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertTrue($map->isEndpointsOccupiedByPlayer($edge, $player));
		$this->assertFalse($map->isEndpointsOccupiedByPlayer($edge, $p2));

		$map->placePiece( 
			$p2, $v2, \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertTrue($map->isEndpointsOccupiedByPlayer($edge, $player));
		$this->assertTrue($map->isEndpointsOccupiedByPlayer($edge, $p2));
	}

	public function testIncidentEdgesOccupiedByPlayer() {
		$map = $this->map;
		$player = $this->player;
		$hexes = $this->hexes;
		$p2 = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$vertex = $hexes[0][0]->getVertex(1);
		$e1 = $hexes[0][0]->getEdge(0);
		$e2 = $hexes[0][0]->getEdge(1);

		$this->assertFalse($map->isIncidentEdgesOccupiedByPlayer($vertex, $player));
		$this->assertFalse($map->isIncidentEdgesOccupiedByPlayer($vertex, $p2));

		$map->placePiece(
			$player, $e1, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertTrue($map->isIncidentEdgesOccupiedByPlayer($vertex, $player));
		$this->assertFalse($map->isIncidentEdgesOccupiedByPlayer($vertex, $p2));

		$map->placePiece(
			$p2, $e2, \Settlers\Constants::BUILD_ROAD
		);

		$this->assertTrue($map->isIncidentEdgesOccupiedByPlayer($vertex, $player));
		$this->assertTrue($map->isIncidentEdgesOccupiedByPlayer($vertex, $p2));
	}
}