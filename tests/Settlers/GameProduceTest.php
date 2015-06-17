<?php
class GameProduceTest extends PHPUnit_Framework_TestCase {
	protected $player;
	protected $game;
	public function setUp()
	{
		$game_reflection = new ReflectionClass('\Settlers\Game');
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$game = new \Settlers\Game(array(
			'room_size' => 4,
			'map_size' => 2
		));

		$prop = $game_reflection->getProperty('map');
		$prop->setAccessible(true);
		$map = $prop->getValue($game);

		$player = new \Settlers\Player(array(
			'user' => 1
		));

		$prop = $map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$game->addPlayer(0, $player);

		$this->game = $game;
		$this->player = $player;
		$this->hexes = $hexes;
	}

	public function testSinglePlayerHexProduce()
	{
		$game = $this->game;
		$player = $this->player;
		$hexes = $this->hexes;

		$hex = $hexes[0][0];
		
		$hex->setTerrain(\Settlers\Constants::TERRAIN_FOREST);
		$game->buildPiece(
			$player, $hex->getVertex(0), \Settlers\Constants::BUILD_SETTLEMENT
		);

		$this->assertEquals(0, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(1, $player->getResourceCount(\Settlers\Constants::RESOURCE_WOOD));

		$hex = $hexes[-1][0];
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(1, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
	}

	public function testMultiplePlayerHexProduce($value='')
	{
		$game = $this->game;
		$player = $this->player;
		$p2 = new \Settlers\Player(array(
			'user' => 1
		));
		$hexes = $this->hexes;
		$game->addPlayer(1, $p2);
		$hex = $hexes[0][0];

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

		$hex = $hexes[-1][0];
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(2, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));

		$hex = $hexes[0][-1];
		$hex->setTerrain(\Settlers\Constants::TERRAIN_PASTURE);
		$game->produceResourcesAtHex($hex);
		$this->assertEquals(3, $player->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
		$this->assertEquals(1, $p2->getResourceCount(\Settlers\Constants::RESOURCE_SHEEP));
	}
}