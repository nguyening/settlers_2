<?php
namespace Settlers;
class Game {
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
	const PLAYER_SETUP = 10;

	const STATE_SETUP_LOBBY = 0;
	const STATE_SETUP_MAP_CREATION = 1;
	const STATE_SETUP_ASSIGNMENTS_SHUFFLE = 2;
	const STATE_SETUP_PLAYER_ORDERING = 3;
	const STATE_SETUP_PLAYER_BUILD_1 = 4;
	const STATE_SETUP_PLAYER_BUILD_2 = 5;
	const STATE_SETUP_DISTRIBUTE = 6;

	const STATE_TURN_START = 7;
	const STATE_TURN_ROLLED = 8;
	const STATE_TURN_FORCE_DISCARD = 9;
	const STATE_TURN_BARON_ACTIVATED = 10;
	const STATE_TURN_BARON_STEALING = 11;

	public $room_size;
	private $map;
	private $state;
	private $players = array();
	private $players_order;
	private $current_turn;
	private $reversed_order = false;

	public function __construct($params = array())
	{
		if(empty($params['map_size']) ||
			empty($params['room_size'])) throw new \Exception('Missing parameter(s).', 1);

		if(filter_var($params['map_size'], FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 0,
					'max_range' => \Settlers\Constants::MAX_SIZE
				)
			)) === false ||
			filter_var($params['room_size'], FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 2,
					'max_range' => 6
				)
			)) === false)
			throw new \Exception('Invalid parameter(s).', 2);

		if(!empty($params['map']) && $params['map'] instanceof \Settlers\Map) {
			$this->map = $params['map'];
		}

		if(isset($params['state']))
			$this->setState($params['state']);
		else
			$this->setState(Game::STATE_SETUP_LOBBY);
		
		$this->room_size = $params['room_size'];
		$this->current_turn = 0;
	}

	public function addPlayer($slot, $player)
	{
		if(!isset($slot) || empty($player)) throw new \Exception('Missing parameter(s).', 1);
		if(!is_int($slot) || 
			!$player instanceof \Settlers\Player) throw new \Exception('Invalid parameter(s).', 2);
		if(!empty($this->players[$slot])) throw new \Exception('This game slot is currently occupied.', 4);
		if(filter_var($slot, FILTER_VALIDATE_INT, array(
				'options' => array(
					'min_range' => 0,
					'max_range' => $this->room_size - 1
				)
			)) === false) throw new \Exception('This game is currently full.', 5);

		$this->players[$slot] = $player;
	}

	public function removePlayer($slot)
	{
		unset($this->players[$slot]);
	}

	public function getPlayer($slot)
	{
		if(empty($this->players[$slot])) return null;
		return $this->players[$slot];
	}

	public function getState()
	{
		return $this->state;
	}

	public function setState($state)
	{
		// TODO: Implement a FSM perhaps to be really strict?
		$this->state = $state;
	}

	/**
	 * GAME LOGIC
	 */

	public function processPlayerAction($player, $action, $args = array())
	{
		if(spl_object_hash($this->players[$this->getPlayerTurn()]) 
			!= spl_object_hash($player)) {
			if($this->state == Game::STATE_TURN_FORCE_DISCARD &&
				$action != Game::PLAYER_DISCARD_RESOURCES)
				throw new \Exception('Invalid action.', 3);

			throw new \Exception('Out-of-turn action.', 6);
		} 
			
		switch($action) {
			case Game::PLAYER_SETUP:
				if(!($this->state == Game::STATE_SETUP_PLAYER_BUILD_1 ||
					$this->state == Game::STATE_SETUP_PLAYER_BUILD_2))
					throw new \Exception('Invalid action.', 3);

				// Count number of types of pieces the player currently has
				$pieces = $player->getPieces();

				switch($args['type']) {
					// Each player gets to build 1 settlement and 1 road during
					// each round of the setup phase.
					case \Settlers\Constants::BUILD_SETTLEMENT:
						$num_settlements = empty($pieces[\Settlers\Constants::BUILD_SETTLEMENT]) ? 0 : count($pieces[\Settlers\Constants::BUILD_SETTLEMENT]);
						
						if(($this->state == Game::STATE_SETUP_PLAYER_BUILD_1 &&
							$num_settlements > 0) ||
							($this->state == Game::STATE_SETUP_PLAYER_BUILD_2 &&
							$num_settlements > 1))
							throw new \Exception('Invalid action.', 3);

						if($this->canSetupPiece($player, $args['location'], $args['type']))
							$this->buildPiece($player, $args['location'], $args['type']);
						else
							throw new \Exception('Invalid action.', 3);
					break;

					case \Settlers\Constants::BUILD_ROAD:
						$num_roads = empty($pieces[\Settlers\Constants::BUILD_ROAD]) ? 0 : count($pieces[\Settlers\Constants::BUILD_ROAD]);
						if(($this->state == Game::STATE_SETUP_PLAYER_BUILD_1 &&
							$num_roads > 0) ||
							($this->state == Game::STATE_SETUP_PLAYER_BUILD_2 &&
							$num_roads > 1))
							throw new \Exception('Invalid action.', 3);

						if($this->canSetupPiece($player, $args['location'], $args['type']))
							$this->buildPiece($player, $args['location'], $args['type']);
						else
							throw new \Exception('Invalid action.', 3);
					break;

					default:
						throw new \Exception('Invalid action.', 3);
					break;
				}
			break;

			case Game::PLAYER_ROLL:
				if($this->state != Game::STATE_TURN_START)
					throw new \Exception('Invalid action.', 3);

				$roll = array_sum($this->rollDice());
				// Enforce 7-roll rule
				if($roll == 7) 
					$this->setState(Game::STATE_TURN_FORCE_DISCARD);
				else
					$this->setState(Game::STATE_TURN_ROLLED);
			break;

			case Game::PLAYER_BUILD:
				if($this->state != Game::STATE_TURN_ROLLED)
					throw new \Exception('Invalid action.', 3);

				if($this->canAfford($player, $args['type']) &&
					$this->canBuildPiece($player, $args['location'], $args['type']))
					$this->buildPiece($players, $args['location'], $args['type']);
			break;

			case Game::PLAYER_BUY_DEVEL:
				if($this->state != Game::STATE_TURN_ROLLED)
					throw new \Exception('Invalid action.', 3);

				if($this->canAfford($player, \Settlers\Constants::BUILD_DEVEL &&
					!$this->isDevelDeckEmpty()))
					$this->drawDevel($player);
			break;

			case Game::PLAYER_PLAY_DEVEL:
				// You can play Knights at the beginning of our turns before rolls.
				if($this->state != Game::STATE_TURN_START &&
					$args['type'] != \Settlers\Constants::DEVEL_KNIGHT)
					throw new \Exception('Invalid action.', 3);

				if($player->getDevelCardsCount($args['type']) > 0) 
					$this->playDevel($player, $args['type']);
			break;

			case Game::PLAYER_MOVE_BARON:
				if($this->state != Game::STATE_TURN_BARON_ACTIVATED)
					throw new \Exception('Invalid action.', 3);

				$this->map->placeBaron($args['hex']);
				$this->setState(Game::STATE_TURN_BARON_STEALING);
			break;

			case Game::PLAYER_STEAL:
				if($this->state != Game::STATE_TURN_BARON_STEALING)
					throw new \Exception('Invalid action.', 3);

				$this->playerSteal($player, $args['player']);
				$this->setState(Game::STATE_TURN_ROLLED);
			break;

			case Game::PLAYER_DISCARD_RESOURCES:
				if($this->state != Game::STATE_TURN_FORCE_DISCARD)
					throw new \Exception('Invalid action.', 3);

				if(empty($args['resources'])) throw new \Exception('Invalid parameter(s).', 2);
				foreach($args['resources'] as $resource => $count) {
					$player->takeResources($resource, $count);
				}
				
				// if($this->isAllPlayersDoneDiscarding())
				// 	$this->setState(Game::STATE_TURN_BARON_ACTIVATED);

			break;

			case Game::PLAYER_END_TURN:
				// If the last player has ended their turn in round 1 of setup
				if($this->state == Game::STATE_SETUP_PLAYER_BUILD_1) {
					if($this->getPlayerTurn() == end($this->players_order)) {
					
						$this->setState(Game::STATE_SETUP_PLAYER_BUILD_2);
						$this->players_order = array_reverse($this->players_order);
					}
				}
				elseif($this->state == Game::STATE_SETUP_PLAYER_BUILD_2) {
					if($this->getPlayerTurn() == end($this->players_order)) {

						$this->players_order = array_reverse($this->players_order);

						$this->setState(Game::STATE_SETUP_DISTRIBUTE);
						// $this->distributeInitialResources();
					}
				}
				else {
					$this->setState(Game::STATE_TURN_START);
				}

				$this->nextPlayerTurn();

				
			break;

			case Game::PLAYER_TRADE:
				// with player or with port
			default:
				throw new Exception('Invalid action.', 3);
			break;	
		}
	}

	public function rollDice($num = 2)
	{
		$rolls = array();
		for($i = 0; $i < $num; $i++) {
			$rolls[] = random_int(0, 6);
		}
		return $rolls;
	}

	public function setupMap($map_size)
	{
		$this->map = new \Settlers\Map(array(
			'map_size' => $map_size
		));
		$this->setState(Game::STATE_SETUP_ASSIGNMENTS_SHUFFLE);
	}

	public function shuffleAssignments($params = array())
	{
		$options = array_merge(array(
			'terrain' => true,
			'chits' => true,
			'ports' => true
		), $params);

		if(!empty($options['terrain']))	$this->map->shuffleTerrain();
		if(!empty($options['chits']))	$this->map->shuffleChits();
		if(!empty($options['ports']))	$this->map->shufflePorts();
	}

	public function determinePlayerOrdering($ordering = array())
	{
		if(!empty($ordering)) {
			if(!empty(array_diff($ordering, array_keys($this->players))))
				throw new \Exception('Invalid parameter.', 2);

			$this->players_order = $ordering;
		}
		else {
			if(empty($this->players)) throw new \Exception('Invalid action.', 3);
			$this->players_order = array_keys($this->players);

			shuffle($this->players_order);
		}

		$this->setState(Game::STATE_SETUP_PLAYER_BUILD_1);
	}

	public function nextPlayerTurn()
	{
		$this->current_turn = ($this->current_turn + 1) % count($this->players);
	}

	public function getPlayerTurn()
	{
		if(empty($this->players_order) || empty($this->players)) throw new \Exception('Invalid action.', 3);
		return $this->players_order[$this->current_turn];
	}

	public function distributeInitialResources()
	{
		$this->setState(Game::STATE_TURN_START);
	}

	public function distributeResources($roll)
	{
		$hexes = $this->map->getProducingHexes($roll);
		foreach($hexes as $idx => $hex) {
			if($this->map->isBaronOnHex($hex))
				continue;

			$this->produceResourcesAtHex($hex);
		}
	}

	public function produceResourcesAtHex($hex)
	{
		if(empty($hex)) throw new \Exception('Missing parameter.', 1);
		if(!$hex instanceof \Settlers\Hex) throw new \Exception('Invalid parameter.', 2);

		$resource = \Settlers\Constants::terrainToResource($hex->getTerrain());
		$player_pieces = $this->map->getPiecesAtHex($hex);
		foreach($this->players as $slot => $player) {
			// If there are no pieces for this player, then continue to the next.
			if(empty($player_pieces[spl_object_hash($player)])) continue;

			$pieces = $player_pieces[spl_object_hash($player)];
			if(isset($pieces[\Settlers\Constants::BUILD_SETTLEMENT]))
				$player->addResources($resource, 1 * count($pieces[\Settlers\Constants::BUILD_SETTLEMENT]));
				
			if(isset($pieces[\Settlers\Constants::BUILD_CITY]))
				$player->addResources($resource, 2 * count($pieces[\Settlers\Constants::BUILD_CITY]));
		}
	}

	public function playerPurchase($player, $build_type)
	{
		if(empty($player) || !isset($build_type)) throw new \Exception('Missing parameter(s).', 1);
		if(!$player instanceof \Settlers\Player || !is_int($build_type))
			throw new \Exception('Invalid parameter(s)', 2);

		foreach(\Settlers\Constants::COST_BUILD[$build_type] as $resource => $amount) {
			$player->takeResources($resource, $amount);
		}
	}

	public function canAfford($player, $build_type)
	{
		if(empty($player) || !isset($build_type)) throw new \Exception('Missing parameter(s).', 1);
		if(!$player instanceof \Settlers\Player || !is_int($build_type))
			throw new \Exception('Invalid parameter(s)', 2);

		foreach(\Settlers\Constants::COST_BUILD[$build_type] as $resource => $amount) {
			if($player->getResourceCount($resource) < $amount) return false;
		}
		return true;
	}

	public function buildPiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		$this->map->placePiece($player, $location, $type);
	}

	public function canSetupPiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		if($location instanceof \Settlers\Vertex) {
			if($type == \Settlers\Constants::BUILD_SETTLEMENT) {
				// A player cannot build on an occupied vertex.
				if($this->map->isVertexOccupied($location))
					return false;

				// All settlements must be 1 vertex away from other vertices
				if($this->map->isAdjacentVerticesOccupied($location))
					return false;

				return true;
			}
		}
		// Default to typical checks for roads
		else 
			return $this->canBuildPiece($player, $location, $type);
	}

	public function canBuildPiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		if($location instanceof \Settlers\Vertex) {
			if($type == \Settlers\Constants::BUILD_CITY) {
				// Cities can only be built on current settlements owned by the player
				if($this->map->isVertexOccupiedByPlayer($location, $player) &&
					$location->getPiece()->getType() == \Settlers\Constants::BUILD_SETTLEMENT)
					return true;

				return false;
			}
			elseif($type == \Settlers\Constants::BUILD_SETTLEMENT) {
				// A player cannot build on an occupied vertex.
				if($this->map->isVertexOccupied($location))
					return false;

				// All settlements must be 1 vertex away from other vertices
				if($this->map->isAdjacentVerticesOccupied($location))
					return false;

				// All settlements must be connected to a road that the player owns
				if($this->map->isIncidentEdgesOccupiedByPlayer($location, $player))
					return true;

				return false;
			}
		}
		elseif($location instanceof \Settlers\Edge && 
			!$this->map->isEdgeOccupied($location) &&
			$type == \Settlers\Constants::BUILD_ROAD) {

			// Roads must be connected to other roads owned by the player
			if($this->map->isAdjacentEdgesOccupiedByPlayer($location, $player))
				return true;

			// Or, roads must be connected to a settlement/city owned by the player
			if($this->map->isEndpointsOccupiedByPlayer($location, $player))
				return true;

			return false;
		}

		return false;
	}
}