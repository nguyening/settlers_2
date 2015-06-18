<?php
namespace Settlers;
abstract class Constants extends BasicEnum {
	const TERRAIN_SEA = 0;
	const TERRAIN_DESERT = 1;		
	const TERRAIN_MOUNTAIN = 2;		// Ore
	const TERRAIN_HILL = 3;			// Brick
	const TERRAIN_FIELD = 4;		// Wheat
	const TERRAIN_PASTURE = 5;		// Sheep
	const TERRAIN_FOREST = 6;		// Wood

	const RESOURCE_ORE = 0;
	const RESOURCE_BRICK = 1;
	const RESOURCE_WHEAT = 2;
	const RESOURCE_SHEEP = 3;
	const RESOURCE_WOOD = 4;
	const RESOURCE_ANY = 5;

	const DEVEL_KNIGHT = 0;
	const DEVEL_MONOPOLY = 1;
	const DEVEL_ROAD_BUILDING = 2;
	const DEVEL_YEAR_OF_PLENTY = 3;
	const DEVEL_VICTORY_POINT = 4;

	const MAX_SIZE = 32;

	const TERRAIN_DISTRIBUTION = array(
		Constants::TERRAIN_MOUNTAIN => 3,
		Constants::TERRAIN_HILL => 3,
		Constants::TERRAIN_FIELD => 4,
		Constants::TERRAIN_PASTURE => 4,
		Constants::TERRAIN_FOREST => 4,
	);
	
	const CHIT_DESERT = 7;
	const CHIT_DISTRIBUTION = array(
		2 => 1,
		3 => 2,
		4 => 2,
		5 => 2,
		6 => 2,
		8 => 2,
		9 => 2,
		10 => 2,
		11 => 2,
		12 => 1
	);

	const PORT_DISTRIBUTION = array(
		Constants::RESOURCE_ORE,-1,-1,-1,
		Constants::RESOURCE_BRICK,-1,-1,
		Constants::RESOURCE_WHEAT,-1,-1,
		Constants::RESOURCE_SHEEP,-1,-1,-1,
		Constants::RESOURCE_WOOD,-1,-1,
		Constants::RESOURCE_ANY,-1,-1,
		Constants::RESOURCE_ANY,-1,-1,-1,
		Constants::RESOURCE_ANY,-1,-1,
		-1,-1,-1
	);

	const BUILD_ROAD = 0;
	const BUILD_SETTLEMENT = 1;
	const BUILD_CITY = 2;
	const BUILD_DEVEL = 3;

	const COST_BUILD = array(
		Constants::BUILD_ROAD => array(
			Constants::RESOURCE_BRICK => 1,
			Constants::RESOURCE_WOOD => 1,
		),
		Constants::BUILD_SETTLEMENT => array(
			Constants::RESOURCE_BRICK => 1,
			Constants::RESOURCE_WHEAT => 1,
			Constants::RESOURCE_SHEEP => 1,
			Constants::RESOURCE_WOOD => 1
		),
		Constants::BUILD_CITY => array(
			Constants::RESOURCE_ORE => 3,
			Constants::RESOURCE_WHEAT => 2,
		),
		Constants::BUILD_DEVEL => array(
			Constants::RESOURCE_WHEAT => 1,
			Constants::RESOURCE_ORE => 1,
			Constants::RESOURCE_SHEEP => 1,
		)
	);

	public function terrainToResource($terrain)
	{
		switch($terrain) {
			case Constants::TERRAIN_MOUNTAIN:
				return Constants::RESOURCE_ORE;
			break;

			case Constants::TERRAIN_HILL:
				return Constants::RESOURCE_BRICK;
			break;

			case Constants::TERRAIN_FIELD:
				return Constants::RESOURCE_WHEAT;
			break;

			case Constants::TERRAIN_PASTURE:
				return Constants::RESOURCE_SHEEP;
			break;

			case Constants::TERRAIN_FOREST:
				return Constants::RESOURCE_WOOD;
			break;

			default:
				return -1;
			break;
		}
	}

	public function constantToLabel($type, $value)
	{
		switch($type) {
			case 'TERRAIN':
				switch($value) {
					case Constants::TERRAIN_SEA: return 'TERRAIN_SEA'; break;
					case Constants::TERRAIN_DESERT: return 'TERRAIN_DESERT'; break;
					case Constants::TERRAIN_MOUNTAIN: return 'TERRAIN_MOUNTAIN'; break;
					case Constants::TERRAIN_HILL: return 'TERRAIN_HILL'; break;
					case Constants::TERRAIN_FIELD: return 'TERRAIN_FIELD'; break;
					case Constants::TERRAIN_PASTURE: return 'TERRAIN_PASTURE'; break;
					case Constants::TERRAIN_FOREST: return 'TERRAIN_FOREST'; break;
				}
			break;

			default:
				return '';
			break;
		}
	}
}