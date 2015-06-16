<?php

class HexTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$hex = new \Settlers\Hex(array());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$hex = new \Settlers\Hex(array('x' => 100, 'y' => -1));
	}

	public function testCreate()
	{
		$hex = new \Settlers\Hex(array('x' => 1, 'y' => 1));
		$this->assertObjectHasAttribute('x', $hex);
		$this->assertObjectHasAttribute('y', $hex);
		$this->assertObjectHasAttribute('chit', $hex);
		$this->assertObjectHasAttribute('terrain', $hex);
		$this->assertObjectHasAttribute('vertices', $hex);
		$this->assertObjectHasAttribute('edges', $hex);

		return $hex;
	}

	/**
	 * @depends testCreate
	 */
	public function testGetNullVertex($hex)
	{
		$this->assertNull($hex->getVertex(0));
		$this->assertNull($hex->getVertex(4));
	}

	/**
	 * @depends 				testCreate
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testAddInvalidVertex($hex)
	{
		$vertex = new \Settlers\Vertex(array(
			'hex' => $hex
		));
		$hex->addVertex(100, $vertex);
	}

	/**
	 * @depends testCreate
	 */
	public function testAddVertex($hex)
	{
		$hex_reflection = new ReflectionClass('\Settlers\Hex');
		$prop = $hex_reflection->getProperty('vertices');
		$prop->setAccessible(true);

		$vertex = new \Settlers\Vertex(array(
			'hex' => $hex
		));
		$hex->addVertex(0, $vertex);
		$hex->addVertex(3, $vertex);

		$this->assertSame($prop->getValue($hex)[0], $vertex);
		$this->assertSame($prop->getValue($hex)[3], $vertex);
	}

	/**
	 * @depends testCreate
	 */
	public function testGetRealVertex($hex)
	{
		$vertex = new \Settlers\Vertex(array(
			'hex' => $hex
		));
		$hex->addVertex(0, $vertex);
		$hex->addVertex(3, $vertex);		

		$this->assertSame($hex->getVertex(0), $vertex);
		$this->assertSame($hex->getVertex(3), $vertex);
	}

	/**
	 * @depends testCreate
	 */
	public function testGetNullEdge($hex)
	{
		$this->assertNull($hex->getEdge(0));
		$this->assertNull($hex->getEdge(4));
	}

	/**
	 * @depends 				testCreate
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testAddInvalidEdge($hex)
	{
		$edge = new \Settlers\Edge(array(
			'hex' => $hex
		));
		$hex->addEdge(100, $edge);
	}

	/**
	 * @depends testCreate
	 */
	public function testAddEdge($hex)
	{
		$hex_reflection = new ReflectionClass('\Settlers\Hex');
		$prop = $hex_reflection->getProperty('edges');
		$prop->setAccessible(true);

		$edge = new \Settlers\Edge(array(
			'hex' => $hex
		));
		$hex->addEdge(0, $edge);
		$hex->addEdge(3, $edge);

		$this->assertSame($prop->getValue($hex)[0], $edge);
		$this->assertSame($prop->getValue($hex)[3], $edge);
	}

	/**
	 * @depends testCreate
	 */
	public function testGetRealEdge($hex)
	{
		$edge = new \Settlers\Edge(array(
			'hex' => $hex
		));
		$hex->addEdge(0, $edge);
		$hex->addEdge(3, $edge);		

		$this->assertSame($hex->getEdge(0), $edge);
		$this->assertSame($hex->getEdge(3), $edge);
	}

	/**
	 * @depends testCreate
	 */
	public function testNullChit($hex)
	{
		$this->assertNull($hex->getChit());
	}

	/**
	 * @depends testCreate
	 */
	public function testSetChit($hex)
	{
		$hex->setChit(10);
		$this->assertNotNull($hex->getChit());
		$this->assertEquals(10, $hex->getChit());
	}

	/**
	 * @depends 				testCreate
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testSetInvalidChit($hex)
	{
		$hex->setChit("a");
	}

	/**
	 * @depends testCreate
	 */
	public function testNullTerrain($hex)
	{
		$this->assertNull($hex->getTerrain());
	}

	/**
	 * @depends testCreate
	 */
	public function testSetTerrain($hex)
	{
		$terrain = \Settlers\Constants::TERRAIN_SEA;

		$hex->setTerrain($terrain);
		$this->assertNotNull($hex->getTerrain());
		$this->assertSame($terrain, $hex->getTerrain());
	}

	/**
	 * @depends 				testCreate
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testSetInvalidTerrain($hex)
	{
		$hex->setTerrain("a");
	}
}