<?php
class GameSetupStatesTest extends PHPUnit_Framework_TestCase {
	protected $game;
	protected $sm;
	protected $map;

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

		$game->processGameAction('finalize_lobby');
		$game->setupMap(2);
		$game->shuffleAssignments();
		$game->processGameAction('finalize_map_assignments');
		$game->determinePlayerOrdering(array(0, 1, 2));

		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('state_machine');
		$prop->setAccessible(true);
		$sm = $prop->getValue($game);

		$this->game = $game;		
		$this->sm = $sm;
		$this->map = $map;
	}

	public function testSetupRound1()
	{
		$game = $this->game;
		$sm = $this->sm;
		$map = $this->map;

		$player = $game->getPlayer(0);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(0)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$player = $game->getPlayer(1);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(2)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(1)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$player = $game->getPlayer(2);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(4)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(4)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_PLAYER_BUILD_2', $current_state->getName());
		return array($game, $sm);
	}

	/**
	 * @depends testSetupRound1
	 */
	public function testSetupRound2($state)
	{
		$game = $state[0];
		$sm = $state[1];

		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$player = $game->getPlayer(2);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(-1, 1)->getEdge(0)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(-1, 1)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$player = $game->getPlayer(1);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(1, -1)->getEdge(5)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(1, -1)->getVertex(0)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$player = $game->getPlayer(0);
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 1)->getVertex(3)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 1)->getEdge(2)
		));
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_END_TURN);

		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_DISTRIBUTE', $current_state->getName());
	}
}