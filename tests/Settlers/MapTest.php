<?php
class MapTest extends PHPUnit_Framework_TestCase {

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$map = new \Settlers\Map(array());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 'a',
		));
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testBaronInvalidPlacement()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 2,
		));

		$map->placeBaron(-100, 1);		
	}

	public function testBaronPlacement()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 2,
		));
		$map_reflection = new ReflectionClass('\Settlers\Map');
		$getHex = $map_reflection->getMethod('getHex');
		$getHex->setAccessible(true);

		$this->assertNull($map->getBaron());
		$map->placeBaron(1, 1);

		$this->assertSame(
			$getHex->invokeArgs($map, array(1, 1)),
			$hex_baron = $map->getBaron()
		);
	}
}