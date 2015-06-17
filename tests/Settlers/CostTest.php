<?php
class CostTest extends PHPUnit_Framework_TestCase {
	protected $player;
	protected $game;

	public function setUp()
	{
		$player = new \Settlers\Player(array(
			'user' => 1
		));
		$map = $this->getMockBuilder('\Settlers\Map')
			->disableOriginalConstructor()
			->getMock();

		$game = new \Settlers\Game(array(
			'map_size' => 2,
			'room_size' => 4,
			'map' => $map
		));

		$this->game = $game;
		$this->player = $player;
	}

	public function testCannotAfford()
	{
		$game = $this->game;
		$player = $this->player;

		foreach(array_keys(\Settlers\Constants::COST_BUILD) as $i => $build_type) {
			$this->assertFalse($game->checkAffordBuild($player, $build_type));
		}
	}

	public function testCanAffordAll()
	{
		$game = $this->game;
		$player = $this->player;

		foreach(array_values(\Settlers\Constants::COST_BUILD) as $i => $resource_counts) {
			foreach($resource_counts as $resource => $count) {
				$player->addResources($resource, $count);
			}
		}

		foreach(array_keys(\Settlers\Constants::COST_BUILD) as $i => $build_type) {
			$this->assertTrue($game->checkAffordBuild($player, $build_type));
		}
	}

	public function testCanAffordSome()
	{
		$game = $this->game;
		$player = $this->player;

		$player->addResources(\Settlers\Constants::RESOURCE_WOOD, 10);
		$player->addResources(\Settlers\Constants::RESOURCE_BRICK, 10);
		$player->addResources(\Settlers\Constants::RESOURCE_WHEAT, 5);


		foreach(array_keys(\Settlers\Constants::COST_BUILD) as $i => $build_type) {
			if($build_type == \Settlers\Constants::BUILD_ROAD)
				$this->assertTrue($game->checkAffordBuild($player, $build_type));
			else
				$this->assertFalse($game->checkAffordBuild($player, $build_type));
		}
	}
}