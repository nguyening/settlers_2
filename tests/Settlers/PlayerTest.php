<?php
class PlayerTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testInvalidCreate()
	{
		$player = new \Settlers\Player();
	}

	public function testCreate()
	{
		$player = new \Settlers\Player(array(
			'user' => 1
		));

		return $player;
	}

	/**
	 * @depends testCreate
	 */
	public function testEmptyHand($player)
	{
		$resource_types = array(
			\Settlers\Constants::RESOURCE_ORE,
			\Settlers\Constants::RESOURCE_BRICK,
			\Settlers\Constants::RESOURCE_WHEAT,
			\Settlers\Constants::RESOURCE_SHEEP,
			\Settlers\Constants::RESOURCE_WOOD
		);
		foreach($resource_types as $i => $resource) {
			$this->assertEquals(0, $player->getResourceCount($resource));
		}

		return $player;
	}

	/**
	 * @depends					testEmptyHand
	 * @expectedException		Exception
	 * @expectedExceptionCode	3
	 */
	public function testTakeEmptyResources($player)
	{
		$player->takeResources(\Settlers\Constants::RESOURCE_ORE, 2);
	}

	/**
	 * @depends testEmptyHand
	 */
	public function testGiveResources($player)
	{
		$this->assertEquals(0, $player->getResourceCount(\Settlers\Constants::RESOURCE_ORE));
		$player->addResources(\Settlers\Constants::RESOURCE_ORE, 2);
		$this->assertEquals(2, $player->getResourceCount(\Settlers\Constants::RESOURCE_ORE));

		return $player;
	}

	/**
	 * @depends testGiveResources
	 */
	public function testTakeResources($player)
	{
		$player->takeResources(\Settlers\Constants::RESOURCE_ORE, 1);
		$this->assertEquals(1, $player->getResourceCount(\Settlers\Constants::RESOURCE_ORE));
	}

	/**
	 * @depends testCreate
	 */
	public function testEmptyPieces($player)
	{
		$hex = $this->getMockBuilder('\Settlers\Hex')
			->disableOriginalConstructor()
			->getMock();

		$this->assertEmpty($player->getPieces());
	}

	/**
	 * @depends testCreate
	 */
	public function testCheckPieces($player)
	{
		$hex = $this->getMockBuilder('\Settlers\Hex')
			->disableOriginalConstructor()
			->getMock();

		$edge = new \Settlers\Edge(array(
			'hex' => $hex
		));

		$vertex = new \Settlers\Vertex(array(
			'hex' => $hex
		));

		$hex->addVertex(0, $vertex);
		$hex->addEdge(0, $edge);

		$settlement = new \Settlers\MapPiece(array(
			'player' => $player,
			'location' => $vertex,
			'type' => \Settlers\Constants::BUILD_SETTLEMENT
		));

		$road = new \Settlers\MapPiece(array(
			'player' => $player,
			'location' => $edge,
			'type' => \Settlers\Constants::BUILD_ROAD
		));

		$this->assertCount(2, $player->getPieces());
	}
}