<?php
class GameTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$game = new \Settlers\Game();
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$game = new \Settlers\Game(array(
			'map_size' => 4,
			'room_size' => 100
		));
	}

	public function testCreate()
	{
		$game = new \Settlers\Game(array(
			'map_size' => 5,
			'room_size' => 4
		));
		$this->assertObjectHasAttribute('map', $game);

		return $game;
	}

	/**
	 * @depends testCreate
	 */
	public function testAddPlayers($game)
	{
		$players = array();
		for($i = 0; $i < 4; $i++) {
			$players[] = $this->getMockBuilder('\Settlers\Player')
				->disableOriginalConstructor()
				->getMock();
		}

		foreach($players as $i => $player) {
			$game->addPlayer($i, $player);
		}

		return $game;
	}


	/**
	 * @depends 				testAddPlayers
	 * @expectedException		Exception
	 * @expectedExceptionCode	4
	 */
	public function testAddReservedPlayer($game)
	{
		$game->addPlayer(1, 
			$this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock()
		);
	}


	/**
	 * @depends 				testAddPlayers
	 * @expectedException		Exception
	 * @expectedExceptionCode	5
	 */
	public function testAddTooManyPlayers($game)
	{
		$game->addPlayer(5, 
			$this->getMockBuilder('\Settlers\Player')
			->disableOriginalConstructor()
			->getMock()
		);
	}
}