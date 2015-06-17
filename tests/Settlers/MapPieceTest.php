<?php
class MapPieceTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$piece = new \Settlers\MapPiece();
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$piece = new \Settlers\MapPiece(array(
			'location' => 1,
			'type' => -1,
			'player' => 1
		));
	}

	public function testCreateMock()
	{
		$player = $this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock();

		$location = $this->getMockBuilder('\Settlers\Edge')
			->disableOriginalConstructor()
			->getMock();

		$piece = new \Settlers\MapPiece(array(
			'location' => $location,
			'type' => \Settlers\Constants::BUILD_ROAD,
			'player' => $player
		));

		$this->assertObjectHasAttribute('type', $piece);
		$this->assertObjectHasAttribute('player', $piece);
		return $piece;
	}
}