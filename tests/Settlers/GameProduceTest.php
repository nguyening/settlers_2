<?php
class GameProduceTest extends PHPUnit_Framework_TestCase {
	protected $player;
	protected $game;
	protected $map;
	public function setUp()
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$game = new \Settlers\Game(array(
			'room_size' => 4,
			'map_size' => 2
		));
		$game->processGameAction('finalize_lobby');
		$game->setupMap(2);
		$game->shuffleAssignments();

		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$player = new \Settlers\Player(array(
			'user' => 1
		));

		$game->addPlayer(0, $player);

		$this->game = $game;
		$this->player = $player;
		$this->map = $map;
	}

	public function testSinglePlayerHexProduce()
	{
		$game = $this->game;
		$map = $this->map;
		$player = $this->player;
		$hex = $map->getHex(0, 0);
		
		$hex->setTerrain(\Settlers\Constants::TERRAIN_FOREST);
		$game->buildPiece(
			$player, $hex->getVertex(0), \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertEquals(0, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(1, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));

		$hex = $map->getHex(0, -1);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(1, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
	}

	public function testMultiplePlayerHexProduce($value='')
	{
		$game = $this->game;
		$map = $this->map;
		$player = $this->player;
		$p2 = new \Settlers\Player(array(
			'user' => 1
		));
		$game->addPlayer(1, $p2);
		$hex = $map->getHex(0, 0);

		$hex->setTerrain(\Settlers\Constants::TERRAIN_FOREST);
		$game->buildPiece(
			$player, $hex->getVertex(0), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$player, $hex->getVertex(1), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$player, $hex->getVertex(2), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(3), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(4), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(5), \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertEquals(0, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$this->assertEquals(0, $p2->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));

		$hex = $map->getHex(0, -1);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(2, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));

		$hex = $map->getHex(-1,0);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
		$this->assertEquals(1, $p2->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
	}

	public function testDistributeOnRoll()
	{
		$game = $this->game;
		$map = $this->map;
		$player = $this->player;
		$p2 = new \Settlers\Player(array(
			'user' => 1
		));
		$game->addPlayer(1, $p2);
		
		$hex = $map->getHex(0,0);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_FOREST);
		$hex->setChit(13);

		$hex = $map->getHex(0, -1);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$hex->setChit(13);

		$hex = $map->getHex(-1, 0);
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$hex->setChit(13);
		
		$hex = $map->getHex(0, 0);
		$game->buildPiece(
			$player, $hex->getVertex(0), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$player, $hex->getVertex(1), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$player, $hex->getVertex(2), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(3), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(4), \Settlers\Constants::BUILD_SETTLEMENT
		);
		$game->buildPiece(
			$p2, $hex->getVertex(5), \Settlers\Constants::BUILD_SETTLEMENT
		);

		$game->distributeResources(13);

		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
		$this->assertEquals(1, $p2->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
	}
}