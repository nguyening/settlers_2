<?php
class VertexTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$vertex = new \Settlers\Vertex(array());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$vertex = new \Settlers\Vertex(array('hex' => 1));
	}

	public function testCreateMock()
	{
		$hex = $this->getMockBuilder('\Settlers\Hex')
			->disableOriginalConstructor()
			->getMock();

		$vertex = new \Settlers\Vertex(array(
			'hex' => $hex
		));
		return $vertex;
	}

	/**
	 * @depends testCreateMock
	 */
	public function testGetNullEdge($vertex) {
		$this->assertNull($vertex->getEdge(0));
		$this->assertNull($vertex->getEdge(2));
	}

	/**
	 * @depends testCreateMock
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testAddInvalidEdge($vertex) {
		$edge = $this->getMockBuilder('\Settlers\Edge')
			->disableOriginalConstructor()
			->getMock();
		$vertex->addEdge(100, $edge);
	}

	/**
	 * @depends testCreateMock
	 */
	public function testAddEdge($vertex)
	{
		$edge = $this->getMockBuilder('\Settlers\Edge')
			->disableOriginalConstructor()
			->getMock();

		$vertex_reflection = new ReflectionClass('\Settlers\Vertex');
		$prop = $vertex_reflection->getProperty('edges');
		$prop->setAccessible(true);

		$vertex->addEdge(1, $edge);
		$this->assertSame(
			$prop->getValue($vertex)[1],
			$vertex->getEdge(1)
		);
	}


	/**
	 * @depends testCreateMock
	 */
	public function testGetRealEdge($vertex) {
		$edge = $this->getMockBuilder('\Settlers\Edge')
			->disableOriginalConstructor()
			->getMock();

		$vertex->addEdge(1, $edge);
		$this->assertSame($edge, $vertex->getEdge(1));
	}
}