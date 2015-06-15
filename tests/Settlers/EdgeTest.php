<?php
class EdgeTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$edge = new \Settlers\Edge(array());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$edge = new \Settlers\Edge(array('hex' => 1));
	}


	public function testCreateMock()
	{
		$hex = $this->getMockBuilder('\Settlers\Hex')
			->disableOriginalConstructor()
			->getMock();

		$edge = new \Settlers\Edge(array(
			'hex' => $hex
		));
		return $edge;
	}

	/**
	 * @depends testCreateMock
	 */
	public function testGetNullVertex($edge) {
		$this->assertNull($edge->getVertex(0));
		$this->assertNull($edge->getVertex("hi"));
	}

	/**
	 * @depends testCreateMock
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testAddInvalidVertex($edge) {
		$vertex = $this->getMockBuilder('\Settlers\Vertex')
			->disableOriginalConstructor()
			->getMock();
		$edge->addVertex(100, $vertex);
	}

	/**
	 * @depends testCreateMock
	 */
	public function testAddVertex($edge)
	{
		$vertex = $this->getMockBuilder('\Settlers\Vertex')
			->disableOriginalConstructor()
			->getMock();

		$edge_reflection = new ReflectionClass('\Settlers\Edge');
		$prop = $edge_reflection->getProperty('vertices');
		$prop->setAccessible(true);

		$edge->addVertex(1, $vertex);
		$this->assertSame(
			$prop->getValue($edge)[1],
			$edge->getVertex(1)
		);
	}


	/**
	 * @depends testCreateMock
	 */
	public function testGetRealVertex($edge) {
		$vertex = $this->getMockBuilder('\Settlers\Vertex')
			->disableOriginalConstructor()
			->getMock();

		$edge->addVertex(1, $vertex);
		$this->assertSame($vertex, $edge->getVertex(1));
	}
}