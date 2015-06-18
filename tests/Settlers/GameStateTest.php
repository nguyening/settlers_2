<?php
class GameStateTest extends PHPUnit_Framework_TestCase {
	protected $game;
	protected $sm;

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

		$game_reflection = new ReflectionClass('\Settlers\Game');
		$prop = $game_reflection->getProperty('state_machine');
		$prop->setAccessible(true);
		$sm = $prop->getValue($game);

		$this->sm = $sm;
		$this->game = $game;
	}

	public function testSetupMap()
	{
		$game = $this->game;
		$sm = $this->sm;

		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_LOBBY', $current_state->getName());

		$game->processGameAction('finalize_lobby');
		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_MAP_CREATION', $current_state->getName());

		$game->setupMap(2);
		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_ASSIGNMENTS_SHUFFLE', $current_state->getName());

		$game->shuffleAssignments();
		$game->processGameAction('finalize_map_assignments');
		
		$game->determinePlayerOrdering(array(0, 1, 2));
		$current_state = $sm->getCurrentState();
		$this->assertEquals('SETUP_PLAYER_BUILD_1', $current_state->getName());

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
		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_SETTLEMENT,
			'location' => $map->getHex(0, 0)->getVertex(0)
		));

		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
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

		$game->processPlayerAction($player, \Settlers\Constants::PLAYER_BUILD, array(
			'type' => \Settlers\Constants::BUILD_ROAD,
			'location' => $map->getHex(0, 0)->getEdge(0)
		));
	}
}