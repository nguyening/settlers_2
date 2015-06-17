<?php
class VertexTest extends PHPUnit_Framework_TestCase {
	public function testCreateMock()
	{
		$hex = $this->getMockBuilder('\Settlers\Hex')
			->disableOriginalConstructor()
			->getMock();

		$vertex = new \Settlers\Vertex();
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

	/**
	 * @depends testCreateMock
	 */
	public function testNullPiece($vertex)
	{
		$this->assertNull($vertex->getPiece());
	}

	/**
	 * @depends 				testCreateMock
	 * @expectedException		Exception
	 * @expectedException		3
	 */
	public function testAddPieceInvalid($vertex)
	{
		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$map_piece = new \Settlers\MapPiece(array(
			'player' => $player,
			'type' => \Settlers\Constants::BUILD_ROAD
		));
		$vertex->setPiece($map_piece);
	}

	/**
	 * @depends 				testCreateMock
	 */
	public function testAddPiece($vertex)
	{
		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$map_piece = new \Settlers\MapPiece(array(
			'location' => $vertex,
			'player' => $player,
			'type' => \Settlers\Constants::BUILD_SETTLEMENT
		));
		$vertex->setPiece($map_piece);

		$this->assertSame($map_piece, $vertex->getPiece());
	}
}