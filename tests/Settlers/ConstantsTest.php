<?php
class ConstantsTest extends PHPUnit_Framework_TestCase {
	public function dataTerrainResources()
	{
		return array(
			array(\Settlers\Constants::TERRAIN_SEA, -1),
			array(\Settlers\Constants::TERRAIN_DESERT, -1),		
			array(\Settlers\Constants::TERRAIN_MOUNTAIN, \Settlers\Constants::RESOURCE_ORE),
			array(\Settlers\Constants::TERRAIN_HILL, \Settlers\Constants::RESOURCE_BRICK),
			array(\Settlers\Constants::TERRAIN_FIELD, \Settlers\Constants::RESOURCE_WHEAT),
			array(\Settlers\Constants::TERRAIN_PASTURE, \Settlers\Constants::RESOURCE_SHEEP),
			array(\Settlers\Constants::TERRAIN_FOREST, \Settlers\Constants::RESOURCE_WOOD)
		);
	}

	/**
	 * @dataProvider dataTerrainResources
	 */
	public function testTerrainToResource($terrain, $resource)
	{
		$this->assertEquals($resource, \Settlers\Constants::terrainToResource($terrain));
	}
}