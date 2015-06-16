<?php
class HexMathTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;

	public function setUp()
	{
		$map = $this->getMockBuilder('\Settlers\Map')
			->disableOriginalConstructor()
			->getMock();
		$map_reflection = new ReflectionClass('\Settlers\Map');

		// Fill in hexes
		$map->map_size = 3;
		$constructHexes = $map_reflection->getMethod('constructHexes');
		$constructHexes->setAccessible(true);
		$constructHexes->invokeArgs($map, array());

		$this->map = $map;
		$this->map_reflection = $map_reflection;
	}

	public function testFillHexes()
	{
		$map = $this->map;
		$map_size = $map->map_size;
		$map_reflection = $this->map_reflection;

		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);

		$count = 0;
		for($y = -1 * $map_size; $y <= $map_size; $y++) {
			$count += count($prop->getValue($map)[$y]);
		}

		// I'll figure out the math property for this..
		$expected = 0;
		for($r = $map_size; $r > 0; $r--) {
			$expected += 2 * $map_size - ($map_size - $r);
		}
		$this->assertEquals(2 * $expected + (2 * $map_size + 1), $count);
	}

	public function testGetInvalidHex()
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);

		$this->assertNull($getHex->invokeArgs($map, array(-10, -10)));
	}

	public function testGetHex()
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);

		$hex = $getHex->invokeArgs($map, array(1, 1));
		$this->assertEquals($hex->x, 1);
		$this->assertEquals($hex->y, 1);
	}

	public function dataCoords()
	{
		return array(
			array(array(0, 0), array(1, -1), \Settlers\Map::HEX_DIR_NE),
			array(array(0, 0), array(1, 0), \Settlers\Map::HEX_DIR_E),
			array(array(0, 0), array(0, 1), \Settlers\Map::HEX_DIR_SE),
			array(array(0, 0), array(-1, 1), \Settlers\Map::HEX_DIR_SW),
			array(array(0, 0), array(-1, 0), \Settlers\Map::HEX_DIR_W),
			array(array(0, 0), array(0, -1), \Settlers\Map::HEX_DIR_NW),

			array(array(1, 1), array(2, 0), \Settlers\Map::HEX_DIR_NE),
			array(array(1, 1), array(2, 1), \Settlers\Map::HEX_DIR_E),
			array(array(1, 1), array(1, 2), \Settlers\Map::HEX_DIR_SE),
			array(array(1, 1), array(0, 2), \Settlers\Map::HEX_DIR_SW),
			array(array(1, 1), array(0, 1), \Settlers\Map::HEX_DIR_W),
			array(array(1, 1), array(1, 0), \Settlers\Map::HEX_DIR_NW),

			array(array(1, 2), array(2, 1), \Settlers\Map::HEX_DIR_NE),
			array(array(1, 2), array(2, 2), \Settlers\Map::HEX_DIR_E),
			array(array(1, 2), array(1, 3), \Settlers\Map::HEX_DIR_SE),
			array(array(1, 2), array(0, 3), \Settlers\Map::HEX_DIR_SW),
			array(array(1, 2), array(0, 2), \Settlers\Map::HEX_DIR_W),
			array(array(1, 2), array(1, 1), \Settlers\Map::HEX_DIR_NW),

			array(array(2, 0), array(3, -1), \Settlers\Map::HEX_DIR_NE),
			array(array(2, 0), array(3, 0), \Settlers\Map::HEX_DIR_E),
			array(array(2, 0), array(2, 1), \Settlers\Map::HEX_DIR_SE),
			array(array(2, 0), array(1, 1), \Settlers\Map::HEX_DIR_SW),
			array(array(2, 0), array(1, 0), \Settlers\Map::HEX_DIR_W),
			array(array(2, 0), array(2, -1), \Settlers\Map::HEX_DIR_NW)
		);
	}

	/**
	 * @dataProvider dataCoords
	 */
	public function testGetCoordinatesInDirection($coords, $neighbor, $dir)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$fn = $map_reflection->getMethod('getCoordinatesInDirection');
		$fn->setAccessible(true);
		$this->assertEquals($neighbor, $fn->invokeArgs($map, array($coords[0], $coords[1], $dir)));
	}

	public function testGetHexInDirection()
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array(1, 1));

		$getHexInDirection = $map_reflection->getMethod('getHexInDirection');
		$getHexInDirection->setAccessible(true);
		$neighbor = $getHexInDirection->invokeArgs($map, array($hex, \Settlers\Map::HEX_DIR_SE));

		$fn = $map_reflection->getMethod('getCoordinatesInDirection');
		$fn->setAccessible(true);
		$this->assertEquals(
			array($neighbor->x, $neighbor->y),
			$fn->invokeArgs($map, array($hex->x, $hex->y, \Settlers\Map::HEX_DIR_SE))
		);
	}

	public function dataCwHexes()
	{
		return array(
			array(array(0, 0), array(0, -1), 0),
			array(array(0, 0), array(1, -1), 1),
			array(array(0, 0), array(1, 0), 2),
			array(array(0, 0), array(0, 1), 3),
			array(array(0, 0), array(-1, 1), 4),
			array(array(0, 0), array(-1, 0), 5),

			array(array(1, 2), array(1, 1), 0),
			array(array(1, 2), array(2, 1), 1),
			array(array(1, 2), array(2, 2), 2),
			array(array(1, 2), array(1, 3), 3),
			array(array(1, 2), array(0, 3), 4),
			array(array(1, 2), array(0, 2), 5)
		);
	}

	/** 
	 * @dataProvider dataCwHexes
	 */
	public function testGetCwHex($hex, $neighbor, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));
		$hex_cw = $getHex->invokeArgs($map, array($neighbor[0], $neighbor[1]));

		$getCwHex = $map_reflection->getMethod('getCwHex');
		$getCwHex->setAccessible(true);

		$this->assertSame(
			$hex_cw,
			$getCwHex->invokeArgs($map, array($hex, $vertex))
		);
	}

	public function dataCcwHexes()
	{
		return array(
			array(array(0, 0), array(-1, 0), 0),
			array(array(0, 0), array(0, -1), 1),
			array(array(0, 0), array(1, -1), 2),
			array(array(0, 0), array(1, 0), 3),
			array(array(0, 0), array(0, 1), 4),
			array(array(0, 0), array(-1, 1), 5),

			array(array(1, 2), array(0, 2), 0),
			array(array(1, 2), array(1, 1), 1),
			array(array(1, 2), array(2, 1), 2),
			array(array(1, 2), array(2, 2), 3),
			array(array(1, 2), array(1, 3), 4),
			array(array(1, 2), array(0, 3), 5)
		);
	}

	/**
	 * @dataProvider dataCcwHexes
	 */
	public function testGetCcwHex($hex, $neighbor, $vertex)
	{
		$map = $this->map;
		$map_reflection = $this->map_reflection;

		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);
		$hex = $getHex->invokeArgs($map, array($hex[0], $hex[1]));
		$hex_ccw = $getHex->invokeArgs($map, array($neighbor[0], $neighbor[1]));

		$getCcwHex = $map_reflection->getMethod('getCcwHex');
		$getCcwHex->setAccessible(true);

		$this->assertSame(
			$hex_ccw,
			$getCcwHex->invokeArgs($map, array($hex, $vertex))
		);
	}
}