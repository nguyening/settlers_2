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

	// public function testCreate()
	// {
	// 	$map_size = 3;
	// 	$map = new \Settlers\Map(array(
	// 		'map_size' => $map_size
	// 	));

	// 	$this->assertObjectHasAttribute('hexes', $map);

	// }

	// /**
	//  * @depends testCreate
	//  */
	// public function testValidGrid($map)
	// {
		
	// }
}