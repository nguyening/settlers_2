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

	const PLAYER_ROLL = 0;
	const PLAYER_BUILD = 1;
	const PLAYER_BUY_DEVEL = 2;
	const PLAYER_PLAY_DEVEL = 3;
	const PLAYER_MOVE_BARON = 4;
	const PLAYER_DISCARD_RESOURCES = 5;
	const PLAYER_EXCHANGE = 6;
	const PLAYER_TRADE = 7;
	const PLAYER_STEAL = 8;
	const PLAYER_END_TURN = 9;

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

/**
 * This flow diagram from a student was helpful, but I couldn't make their rectangles map directly to states
 * and edges to transitions
 * https://engineering.purdue.edu/ece477/Archive/2013/Spring/S13-Grp03/images/nb/jhunsber/CatanLogic.png 
 */

	// const STATE_SETUP_LOBBY = 0;
	// const STATE_SETUP_MAP_CREATION = 1;
	// const STATE_SETUP_ASSIGNMENTS_SHUFFLE = 2;
	// const STATE_SETUP_PLAYER_ORDERING = 3;
	// const STATE_SETUP_PLAYER_BUILD_1 = 4;
	// const STATE_SETUP_PLAYER_BUILD_2 = 5;
	// const STATE_SETUP_DISTRIBUTE = 6;
	// const STATE_GAME_START = 7;

	// const STATE_TURN_START = 8;
	// const STATE_TURN_BARON_PLACE = 10;
	// const STATE_TURN_BARON_STEAL = 11;
	// const STATE_TURN_ROLL = 11;
	// const STATE_TURN_ROLLED_7 = 12;
	// const STATE_TURN_RESOURCE_DISTIBUTION = 13;
	// const STATE_TURN_MAIN_PHASE = 14;
	// const STATE_TURN_END = 15;

	const STATE_TRANSITIONS = array(
	    'class' => '\Settlers\State',
	    'states'  => array(
	        'SETUP_LOBBY' => array(
		        'type' =>		'initial',
		        'properties' => array()
	        ),
			'SETUP_MAP_CREATION' => array(
				'type' =>		'normal',
				'properties' => array(
					'map_generatable' => true
				)
			),
			'SETUP_ASSIGNMENTS_SHUFFLE' => array(
				'type' =>		'normal',
				'properties' => array(
					array(
						'terrain_shufflable' => true,
						'chits_shufflable' => true,
						'ports_shufflable' => true
					)
				)
			),
			'SETUP_PLAYER_ORDERING' => array(
				'type' =>		'normal',
				'properties' => array(
					'players_order_shuffable' => true
				)
			),
			'SETUP_PLAYER_BUILD_1' => array(
				'type' =>		'normal',
				'properties' => array(
					'road_buildable' => true,
					'settlement_buildable' => true,
					'end_turn_able' => true,
					'free_building' => true,
					'relaxed_settlement_restrictions' => true,
					'setup_turn' => true,

					'max_settlements' => 1,
					'max_roads' => 1,
					'max_cities' => 0
				)
			),
			'SETUP_PLAYER_BUILD_2' => array(
				'type' =>		'normal',
				'properties' => array(
					'road_buildable' => true,
					'settlement_buildable' => true,
					'end_turn_able' => true,
					'free_building' => true,
					'relaxed_settlement_restrictions' => true,
					'setup_turn' => true,
					'players_order_reversed' => true,
					'last_setup_state' => true,

					'max_settlements' => 2,
					'max_roads' => 2,
					'max_cities' => 0

				)
			),
			'SETUP_DISTRIBUTE' => array(
				'type' =>		'normal',
				'properties' => array(
					'resources_initiable' => true
				)
			),
			'GAME_START' => array(
				'type' =>		'normal',
				'properties' => array()
			),

			'TURN_START' => array(
				'type' =>		'normal',
				'properties' => array(
					'knight_playable' => true,
					'dice_rollable' => true,
				)
			),
			'TURN_PREROLL_BARON_PLACE' => array(
				'type' =>		'normal',
				'properties' => array(
					'baron_placeable' => true
				)
			),
			'TURN_PREROLL_BARON_STEAL' => array(
				'type' =>		'normal',
				'properties' => array(
					'player_stealable' => true
				)
			),
			'TURN_ROLL' => array(
				'type' =>		'normal',
				'properties' => array(
					'dice_rollable' => true
				)
			),

			'TURN_ROLLED_7' => array(
				'type' =>		'normal',
				'properties' => array(
					'7_rule_enforcable' => true,
					'out_of_turn_acceptable' => true
				)
			),
			'TURN_POSTROLL_BARON_PLACE' => array(
				'type' =>		'normal',
				'properties' => array(
					'baron_placeable' => true
				)
			),
			'TURN_POSTROLL_BARON_STEAL' => array(
				'type' =>		'normal',
				'properties' => array(
					'player_stealable' => true
				)
			),

			'TURN_RESOURCE_DISTRIBUTION' => array(
				'type' =>		'normal',
				'properties' => array(
					'resources_distributable' => true
				)
			),

			'TURN_MAIN_PHASE' => array(
				'type' =>		'normal',
				'properties' => array(
					'knight_playable' => true,
					'devel_playable' => true,
					'road_buildable' => true,
					'settlement_buildable' => true,
					'city_buildable' => true,
					'devel_buildable' => true,
					'resources_tradable' => true,
					'resources_exchangable' => true,
					'end_turn_able' => true,

					'max_settlements' => 5,
					'max_cities' => 4,
					'max_roads' => 15
				)
			),
			'TURN_END' => array(
				'type' =>		'normal',
				'properties' => array(
					'win_checkable' => true
				)
			)
	    ),

		// Transitions allow for an action to be done from 
		// multiple states-to-one, but I don't have a need for it.
	    'transitions' => array(
	    	'finalize_lobby' => array(
	    		'from' => array('SETUP_LOBBY'),
	    		'to' => 'SETUP_MAP_CREATION'
	    	),
	    	'finalize_map_load' => array(
	    		'from' => array('SETUP_MAP_CREATION'),
	    		'to' => 'SETUP_ASSIGNMENTS_SHUFFLE'
    		),
    		'finalize_map_assignments' => array(
    			'from' => array('SETUP_ASSIGNMENTS_SHUFFLE'),
    			'to' => 'SETUP_PLAYER_ORDERING'
    		),
    		'finalize_players_order' => array(
    			'from' => array('SETUP_PLAYER_ORDERING'),
    			'to' => 'SETUP_PLAYER_BUILD_1'
    		),
    		'end_setup_round_1' => array(
    			'from' => array('SETUP_PLAYER_BUILD_1'),
    			'to' => 'SETUP_PLAYER_BUILD_2'
    		),
    		'end_setup_round_2' => array(
    			'from' => array('SETUP_PLAYER_BUILD_2'),
    			'to' => 'SETUP_DISTRIBUTE'
    		),
    		'finalize_setup' => array(
    			'from' => array('SETUP_DISTRIBUTE'),
    			'to' => 'GAME_START'
    		),
    		
    		'start_game' => array(
    			'from' => array('GAME_START'),
    			'to' => 'TURN_START'
    		),
			'play_preroll_knight' => array(
				'from' => array('TURN_START'),
				'to' => 'TURN_PREROLL_BARON_PLACE'
			),

			'place_preroll_baron' => array(
				'from' => array('TURN_PREROLL_BARON_PLACE'),
				'to' => 'TURN_PREROLL_BARON_STEAL'
			),
			'steal_preroll_baron' => array(
				'from' => array('TURN_PREROLL_BARON_STEAL'),
				'to' => 'TURN_ROLL'
			),

			'roll_regular' => array(
				'from' => array('TURN_ROLL', 'TURN_START'),
				'to' => 'TURN_RESOURCE_DISTRIBUTION'
			),
			'roll_7' => array(
				'from' => array('TURN_ROLL', 'TURN_START'),
				'to' => 'TURN_ROLLED_7'
			),
			'enforced_7_rule' => array(
				'from' => array('TURN_ROLLED_7'),
				'to' => 'TURN_POSTROLL_BARON_PLACE'
			),

			
			'distribute_resources' => array(
				'from' => array('TURN_RESOURCE_DISTRIBUTION'),
				'to' => 'TURN_MAIN_PHASE'
			),
			'play_postroll_knight' => array(
				'from' => array('TURN_MAIN_PHASE'),
				'to' => 'TURN_POSTROLL_BARON_PLACE'
			),
			'end_turn' => array(
				'from' => array('TURN_MAIN_PHASE'),
				'to' => 'TURN_END'
			),
			'start_next_turn' => array(
				'from' => array('TURN_END'),
				'to' => 'TURN_START'
			),

			'place_baron' => array(
				'from' => array('TURN_POSTROLL_BARON_PLACE'),
				'to' => 'TURN_POSTROLL_BARON_STEAL'
			),
			'steal_baron' => array(
				'from' => array('TURN_POSTROLL_BARON_STEAL'),
				'to' => 'TURN_MAIN_PHASE'
			)
	    ),
	);

	public function actionToStateProperty($action, $args = array())
	{
		// GAME_GENERATE_MAP
		// GAME_SHUFFLE_TERRAIN
		// GAME_SHUFFLE_CHITS
		// GAME_SHUFFLE_PORTS
		// GAME_SHUFFLE_PLAYERS

		// return 'map_generatable';
		// return 'terrain_shufflable';
		// return 'chits_shufflable';
		// return 'ports_shufflable';
		// return 'players_order_shuffable';
		// return 'resources_initiable';
		// return 'out_of_turn_acceptable';
		// return 'resources_distributable';
		// return 'win_checkable';
		switch($action) {
			case Constants::PLAYER_BUILD:
				switch($args['type']) {
					case Constants::BUILD_ROAD:
						return 'road_buildable';
					break;
					case Constants::BUILD_SETTLEMENT:
						return 'settlement_buildable';
					break;
					case Constants::BUILD_CITY:
						return 'city_buildable';
					break;
				}
			break;

			case Constants::PLAYER_BUY_DEVEL:
				return 'devel_buildable';
			break;

			case Constants::PLAYER_ROLL:
				return 'dice_rollable';
			break;

			case Constants::PLAYER_PLAY_DEVEL:
				switch($args['type']) {
					case Constants::DEVEL_KNIGHT:
						return 'knight_playable';
					break;
					default:
						return 'devel_playable';
					break;
				}
			break;

			case Constants::PLAYER_DISCARD_RESOURCES:
				return '7_rule_enforcable';
			break;

			case Constants::PLAYER_MOVE_BARON:
				return 'baron_placeable';
			break;

			case Constants::PLAYER_STEAL:
				return 'player_stealable';
			break;

			case Constants::PLAYER_TRADE:
				return 'resources_tradable';
			break;

			case Constants::PLAYER_EXCHANGE:
				return 'resources_exchangable';
			break;

			case Constants::PLAYER_END_TURN:
				return 'end_turn_able';
			break;
		}

		return 'invalid_action';
		
	}

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