<?php
class HexNetworkTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;

	public function setUp()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 5
		));
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$this->map = $map;
		$this->map_reflection = $map_reflection;
	}

	public function testFullNetwork()
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);

		// $count = array(
		// 	'faces' => array(),
		// 	'edges' => array(),
		// 	'vertices' => array()
		// );

		for($y = -1 * $map->map_size; $y <= $map->map_size; $y++) {
			for($x = -1 * $map->map_size; $x <= $map->map_size; $x++) {
				$hex = $getHex->invokeArgs($map, array($x, $y));

				for($i = 0; $i < 6; $i++) {
					$this->assertNotNull($hex->getVertex($i));
					$this->assertNotNull($hex->getEdge($i));

					// Using object hash to get all unique faces, edges, and vertices
					// $count['faces'][spl_object_hash($hex)] = 1;
					// for($idx = 0; $idx < 6; $idx++) {
					// 	$count['vertices'][spl_object_hash($hex->getVertex($idx))] = 1;
					// 	$count['edges'][spl_object_hash($hex->getEdge($idx))] = 1;
					// }
				}
			}
		}

		// Math properties of parts for a hexagonal grid
		// -- These properties don't work because we are actually making 
		// a few more hexes than a perfect hexagonal grid would contain.

		// $this->assertCount(count($count['faces']) * 3, $count['edges']);
		// $this->assertCount(count($count['faces']) * 2, $count['vertices']);
	}

	public function dataCwHexVertices()
	{
		return array(
			array(array(0, 0), array(0, -1), 4, 0),
			array(array(0, 0), array(1, -1), 5, 1),
			array(array(0, 0), array(1, 0), 0, 2),
			array(array(0, 0), array(0, 1), 1, 3),
			array(array(0, 0), array(-1, 1), 2, 4),
			array(array(0, 0), array(-1, 0), 3, 5),

			array(array(1, 2), array(1, 1), 4, 0),
			array(array(1, 2), array(2, 1), 5, 1),
			array(array(1, 2), array(2, 2), 0, 2),
			array(array(1, 2), array(1, 3), 1, 3),
			array(array(1, 2), array(0, 3), 2, 4),
			array(array(1, 2), array(0, 2), 3, 5)
		);
	}

	/**
	 * @dataProvider dataCwHexVertices
	 */
	public function testGetCwHexVertex($hex, $neighbor, $neighbor_vertex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));
		$hex_cw = $getHex->invokeArgs($map, array($neighbor[0], $neighbor[1]));

		$getCwHexVertex = $map_reflection->getMethod('getCwHexVertex');
		$getCwHexVertex->setAccessible(true);

		$this->assertSame(
			$hex_cw->getVertex($neighbor_vertex),
			$getCwHexVertex->invokeArgs($map, array($hex, $vertex))
		);
	}

	public function dataCcwHexVertices()
	{
		return array(
			array(array(0, 0), array(-1, 0), 2, 0),
			array(array(0, 0), array(0, -1), 3, 1),
			array(array(0, 0), array(1, -1), 4, 2),
			array(array(0, 0), array(1, 0), 5, 3),
			array(array(0, 0), array(0, 1), 0, 4),
			array(array(0, 0), array(-1, 1), 1, 5),

			array(array(1, 2), array(0, 2), 2, 0),
			array(array(1, 2), array(1, 1), 3, 1),
			array(array(1, 2), array(2, 1), 4, 2),
			array(array(1, 2), array(2, 2), 5, 3),
			array(array(1, 2), array(1, 3), 0, 4),
			array(array(1, 2), array(0, 3), 1, 5)
		);
	}

	/**
	 * @dataProvider dataCcwHexVertices
	 */
	public function testGetCcwHexVertex($hex, $neighbor, $neighbor_vertex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));
		$hex_ccw = $getHex->invokeArgs($map, array($neighbor[0], $neighbor[1]));

		$getCcwHexVertex = $map_reflection->getMethod('getCcwHexVertex');
		$getCcwHexVertex->setAccessible(true);

		$this->assertSame(
			$hex_ccw->getVertex($neighbor_vertex),
			$getCcwHexVertex->invokeArgs($map, array($hex, $vertex))
		);
	}

	public function dataCwHexEdges()
	{
		return array(
			array(array(0, 0), array(0, -1), 3, 0),
			array(array(0, 0), array(1, -1), 4, 1),
			array(array(0, 0), array(1, 0), 5, 2),
			array(array(0, 0), array(0, 1), 0, 3),
			array(array(0, 0), array(-1, 1), 1, 4),
			array(array(0, 0), array(-1, 0), 2, 5),

			array(array(1, 2), array(1, 1), 3, 0),
			array(array(1, 2), array(2, 1), 4, 1),
			array(array(1, 2), array(2, 2), 5, 2),
			array(array(1, 2), array(1, 3), 0, 3),
			array(array(1, 2), array(0, 3), 1, 4),
			array(array(1, 2), array(0, 2), 2, 5)
		);
	}

	/**
	 * @dataProvider dataCwHexEdges
	 */
	public function testGetCwHexEdge($hex, $neighbor, $neighbor_edge, $edge)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));
		$hex_cw = $getHex->invokeArgs($map, array($neighbor[0], $neighbor[1]));

		$getCwHexEdge = $map_reflection->getMethod('getCwHexEdge');
		$getCwHexEdge->setAccessible(true);

		$this->assertSame(
			$hex_cw->getEdge($neighbor_edge),
			$getCwHexEdge->invokeArgs($map, array($hex, $edge))
		);
	}

	public function dataVertexEdges()
	{
		return array(
			array(array(0, 0), 0),
			array(array(0, 0), 1),
			array(array(0, 0), 2),
			array(array(0, 0), 3),
			array(array(0, 0), 4),
			array(array(0, 0), 5),

			array(array(1, 2), 0),
			array(array(1, 2), 1),
			array(array(1, 2), 2),
			array(array(1, 2), 3),
			array(array(1, 2), 4),
			array(array(1, 2), 5)
		);
	}

	/**
	 * @dataProvider dataVertexEdges
	 */
	public function testGetVertexCwEdge($hex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));

		$getVertexCwEdge = $map_reflection->getMethod('getVertexCwEdge');
		$getVertexCwEdge->setAccessible(true);

		$this->assertSame(
			$hex->getEdge($vertex),
			$getVertexCwEdge->invokeArgs($map, array($hex, $vertex))
		);
	}

	/**
	 * @dataProvider dataVertexEdges
	 */
	public function testGetVertexCcwEdge($hex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));

		$getVertexCcwEdge = $map_reflection->getMethod('getVertexCcwEdge');
		$getVertexCcwEdge->setAccessible(true);

		$this->assertNotSame(
			$hex->getEdge($vertex),
			$getVertexCcwEdge->invokeArgs($map, array($hex, $vertex))
		);
	}

	/**
	 * @dataProvider dataVertexEdges
	 */
	public function testGetVertexOppositeEdge($hex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));

		$getVertexOppositeEdge = $map_reflection->getMethod('getVertexOppositeEdge');
		$getVertexOppositeEdge->setAccessible(true);

		$getCwHex = $map_reflection->getMethod('getCwHex');
		$getCwHex->setAccessible(true);

		if($getCwHex->invokeArgs($map, array($hex, $vertex)) != null) {
			$this->assertNotSame(
				$hex->getEdge($vertex),
				$getVertexOppositeEdge->invokeArgs($map, array($hex, $vertex))
			);		
		}
	}

	/**
	 * @dataProvider dataVertexEdges
	 */
	public function testVertexConnectUniqueEdges($hex, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));

		$getVertexCwEdge = $map_reflection->getMethod('getVertexCwEdge');
		$getVertexCwEdge->setAccessible(true);
		$getVertexCcwEdge = $map_reflection->getMethod('getVertexCcwEdge');
		$getVertexCcwEdge->setAccessible(true);
		$getVertexOppositeEdge = $map_reflection->getMethod('getVertexOppositeEdge');
		$getVertexOppositeEdge->setAccessible(true);

		$cw_edge = $getVertexCwEdge->invokeArgs($map, array($hex, $vertex));
		$ccw_edge = $getVertexCcwEdge->invokeArgs($map, array($hex, $vertex));
		$op_edge = $getVertexOppositeEdge->invokeArgs($map, array($hex, $vertex));
		
		$this->assertNotSame($ccw_edge, $cw_edge);
		$this->assertNotSame($ccw_edge, $op_edge);
		$this->assertNotSame($cw_edge, $op_edge);
	}

	/**
	 * @dataProvider dataVertexEdges
	 */
	public function testGetEdgeOppositeVertex($hex, $edge)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));

		$getEdgeOppositeVertex = $map_reflection->getMethod('getEdgeOppositeVertex');
		$getEdgeOppositeVertex->setAccessible(true);

		$this->assertNotSame(
			$hex->getVertex($edge),
			$getEdgeOppositeVertex->invokeArgs($map, array($hex, $edge))
		);
	}

	public function testConnectedNetwork($value='')
	{
		$map = $this->map;

		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);

		$getCwHex = $map_reflection->getMethod('getCwHex');
		$getCwHex->setAccessible(true);

		for($y = -1 * $map->map_size; $y <= $map->map_size; $y++) {
			for($x = -1 * $map->map_size; $x <= $map->map_size; $x++) {
				$hex = $getHex->invokeArgs($map, array($x, $y));

				for($i = 0; $i < 6; $i++) {
					$vertex = $hex->getVertex($i);
					$edge = $hex->getEdge($i);

					$this->assertNotNull($vertex->getEdge(0));
					$this->assertNotNull($vertex->getEdge(1));
					if($getCwHex->invokeArgs($map, array($hex, $i)) != null) {
						$this->assertNotNull($vertex->getEdge(2));
					}

					$this->assertNotNull($edge->getVertex(0));
					$this->assertNotNull($edge->getVertex(1));
				}
			}
		}
	}
}