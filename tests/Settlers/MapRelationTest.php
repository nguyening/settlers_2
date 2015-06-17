<?php
class MapRelationTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;
	protected $hexes;

	public function setUp()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 2
		));
		$map_reflection = new ReflectionClass('\Settlers\Map');
		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$this->map = $map;
		$this->map_reflection = $map_reflection;
		$this->hexes = $hexes;
	}

	public function testBoundaryComponents()
	{
		$hexes = $this->hexes;
		$map_reflection = $this->map_reflection;
		$count = array(
			'edges' => 0,
			'vertices' => 0
		);

		$isBoundaryEdge = $map_reflection->getMethod('isBoundaryEdge');
		$isBoundaryEdge->setAccessible(true);
		$isBoundaryVertex = $map_reflection->getMethod('isBoundaryVertex');
		$isBoundaryVertex->setAccessible(true);

		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				for($i = 0; $i < 6; $i++) {
					if($isBoundaryEdge->invokeArgs($this->map, array($hex->getEdge($i))))
						$count['edges']++;
					if($isBoundaryVertex->invokeArgs($this->map, array($hex->getVertex($i))))
						$count['vertices']++;
				}
			}
		}

		$this->assertEquals(30, $count['edges']);
		$this->assertEquals(28, $count['vertices']);
	}

	public function testAdjacentVertex()
	{
		$hexes = $this->hexes;
		$isAdjacentVertex = $this->map_reflection->getMethod('isAdjacentVertex');
		$isAdjacentVertex->setAccessible(true);

		$v1 = $hexes[0][0]->getVertex(0);
		$v2 = $hexes[0][0]->getVertex(1);
		$v3 = $hexes[0][2]->getVertex(0);

		$this->assertTrue($isAdjacentVertex->invokeArgs($this->map, array($v1, $v2)));
		$this->assertFalse($isAdjacentVertex->invokeArgs($this->map, array($v1, $v3)));
	}

	public function testAdjacentVertices()
	{
		$hexes = $this->hexes;
		$vertex = $hexes[0][0]->getVertex(0);

		$getAdjacentVertices = $this->map_reflection->getMethod('getAdjacentVertices');
		$getAdjacentVertices->setAccessible(true);
		$neighbors = $getAdjacentVertices->invokeArgs($this->map, array($vertex));

		$isAdjacentVertex = $this->map_reflection->getMethod('isAdjacentVertex');
		$isAdjacentVertex->setAccessible(true);

		foreach($neighbors as $idx => $neighbor) {
			$this->assertTrue($isAdjacentVertex->invokeArgs($this->map, array($vertex, $neighbor)));
		}
	}

	public function testAdjacentEdge()
	{
		$hexes = $this->hexes;
		$e1 = $hexes[-1][0]->getEdge(1);
		$e2 = $hexes[-2][1]->getEdge(4);
		$e3 = $hexes[0][-1]->getEdge(3);

		$isAdjacentEdge = $this->map_reflection->getMethod('isAdjacentEdge');
		$isAdjacentEdge->setAccessible(true);

		$this->assertTrue($isAdjacentEdge->invokeArgs($this->map, array($e1, $e2)));
		$this->assertFalse($isAdjacentEdge->invokeArgs($this->map, array($e1, $e3)));
	}

	public function testAdajcentEdges()
	{
		$hexes = $this->hexes;
		$edge = $hexes[-1][-1]->getEdge(0);
		
		$getAdjacentEdges = $this->map_reflection->getMethod('getAdjacentEdges');
		$getAdjacentEdges->setAccessible(true);

		$isAdjacentEdge = $this->map_reflection->getMethod('isAdjacentEdge');
		$isAdjacentEdge->setAccessible(true);

		foreach($getAdjacentEdges->invokeArgs($this->map, array($edge))
			as $idx => $neighbor) {
			$this->assertTrue($isAdjacentEdge->invokeArgs($this->map, array($edge, $neighbor)));
		}
	}
}