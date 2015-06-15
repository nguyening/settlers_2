<?php
namespace Settlers;
abstract class Constants extends BasicEnum {
	const TERRAIN_SEA = 0;
	const TERRAIN_DESERT = 1;		
	const TERRAIN_MOUNTAIN = 2;		// Ore
	const TERRAIN_HILL = 3;			// Brick
	const TERRAIN_FIELD = 4;		// Wheat
	const TERRAIN_PASTURE = 4;		// Sheep
	const TERRAIN_FOREST = 5;		// Wood

	const RESOURCE_ORE = 0;
	const RESOURCE_BRICK = 1;
	const RESOURCE_WHEAT = 2;
	const RESOURCE_SHEEP = 3;
	const RESOURCE_WOOD = 4;

	const MAX_SIZE = 32;
}