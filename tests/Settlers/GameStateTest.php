<?php
class GameStateTest extends PHPUnit_Framework_TestCase {
	protected $game;

	public function setUp()
	{
		$game = new \Settlers\Game(array(
			'room_size' => 4,
			'map_size' => 2
		));
		
		for($i = 0; $i < 3; $i++) {
			$game->addPlayer($i, new \Settlers\Player(array(
				'user' => $i + 1
			)));
		}

		$this->game = $game;
	}

	public function testSetupMap()
	{
		$game = $this->game;
		$this->assertEquals(\Settlers\Game::STATE_SETUP_LOBBY, $game->getState());

		$game->setState(\Settlers\Game::STATE_SETUP_MAP_CREATION);
		$this->assertEquals(\Settlers\Game::STATE_SETUP_MAP_CREATION, $game->getState());

		$game->setupMap(2);
		$this->assertEquals(\Settlers\Game::STATE_SETUP_ASSIGNMENTS_SHUFFLE, $game->getState());

		$game->shuffleAssignments();
		$game->setState(\Settlers\Game::STATE_SETUP_PLAYER_ORDERING);
		$game->determinePlayerOrdering(array(0, 1, 2));
		$this->assertEquals(\Settlers\Game::STATE_SETUP_PLAYER_BUILD_1, $game->getState());

		return $game;
	}

	/**
	 * @depends 				testSetupMap
	 * @expectedException		Exception
	 * @expectedExceptionCode	3
	 */
	public function testPlaygroundBuildingInvalid($game)
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);
		$player = $game->getPlayer(0);

		// Player tries to build two settlements on first turn
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(0)
		));

		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(4)
		));
	}

	/**
	 * @depends 				testSetupMap
	 * @expectedException		Exception
	 * @expectedExceptionCode	6
	 */
	public function testOutOfTurn($game)
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);
		$player = $game->getPlayer(1);

		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(0)
		));
	}


	public function testSetupRound1()
	{
		$game = $this->game;
		$game->setState(\Settlers\Game::STATE_SETUP_MAP_CREATION);
		$game->setupMap(2);
		$game->shuffleAssignments();
		$game->setState(\Settlers\Game::STATE_SETUP_PLAYER_ORDERING);
		$game->determinePlayerOrdering(array(0, 1, 2));

		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$player = $game->getPlayer(0);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(0)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);

		$player = $game->getPlayer(1);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(2)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(1)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);

		$player = $game->getPlayer(2);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(4)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(4)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);

		$this->assertEquals(\Settlers\Game::STATE_SETUP_PLAYER_BUILD_2, $game->getState());
		return $game;
	}

	/**
	 * @depends testSetupRound1
	 */
	public function testSetupRound2($game)
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);
		$player = $game->getPlayer(2);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(-1, 1)->getEdge(0)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(-1, 1)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);

		$player = $game->getPlayer(1);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(1, -1)->getEdge(5)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(1, -1)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);

		$player = $game->getPlayer(0);
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 1)->getVertex(3)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_SETUP, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 1)->getEdge(2)
		));
		$game->processPlayerAction($player, \Settlers\Game::PLAYER_END_TURN);
		$this->assertEquals(\Settlers\Game::STATE_SETUP_DISTRIBUTE, $game->getState());
	}
}