<?php
namespace Settlers;
class Game {
	public $room_size;
	private $map;
	private $state_machine;

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

		/*if(isset($params['state']))
			$this->setState($params['state']);
		else
			$this->setState(Game::STATE_SETUP_LOBBY);
		*/

		// Game properties
		$this->room_size = $params['room_size'];
		$this->current_turn = 0;

		// Game flow logic
		$state = new \Settlers\State();
		$sm = new \Finite\StateMachine\StateMachine($state);
		$loader = new \Finite\Loader\ArrayLoader(\Settlers\Constants::STATE_TRANSITIONS);

		$loader->load($sm);
		$sm->initialize();
		$this->state_machine = $sm;
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

	/**
	 * GAME LOGIC
	 */

	public function processGameAction($action)
	{
		$sm = $this->state_machine;
		$current_state = $sm->getCurrentState();
		if(!$current_state->can($action))
			throw new \Exception('Invalid action.', 3);

		$sm->apply($action);
	}

	public function processPlayerAction($player, $action, $args = array())
	{
		$sm = $this->state_machine;
		$current_state = $sm->getCurrentState();

		if(!($this->isPlayerCurrentPlayer($player) || $current_state->has('out_of_turn_acceptable')))
			throw new \Exception('Out-of-turn action.', 6);

		if(!$current_state->has(\Settlers\Constants::actionToStateProperty($action, $args)))
			throw new \Exception('Invalid action.', 3);
			
		switch($action) {
			case \Settlers\Constants::PLAYER_BUILD:
				// Count number of types of pieces the player currently has
				$pieces = $player->getPieces();
				
				switch($args['type']) {
					case \Settlers\Constants::BUILD_SETTLEMENT:
						$num_pieces = empty($pieces[\Settlers\Constants::BUILD_SETTLEMENT]) ? 0 : count($pieces[\Settlers\Constants::BUILD_SETTLEMENT]);
						$max_pieces = $current_state->get('max_settlements');
					break;

					case \Settlers\Constants::BUILD_ROAD:
						$num_pieces = empty($pieces[\Settlers\Constants::BUILD_ROAD]) ? 0 : count($pieces[\Settlers\Constants::BUILD_ROAD]);
						$max_pieces = $current_state->get('max_roads');
					break;

					case \Settlers\Constants::BUILD_CITY:
						$num_pieces = empty($pieces[\Settlers\Constants::BUILD_CITY]) ? 0 : count($pieces[\Settlers\Constants::BUILD_CITY]);
						$max_pieces = $current_state->get('max_cities');
					break;
				}
				
				// Players have relaxed restrictions on settlements during setup.
				$can_build = $current_state->has('relaxed_settlement_restrictions')
					? $this->canSetupPiece($player, $args['location'], $args['type'])
					: $this->canBuildPiece($player, $args['location'], $args['type']);

				// Allow building if:
				//	(1) the player can afford it
				//	(2) the player has enough pieces for it
				//	(3) the player can build in the location
				if(($current_state->has('free_building') || $this->canAfford($player, $args['type'])) &&
					$num_pieces < $max_pieces &&
					$can_build) {
					$this->buildPiece($player, $args['location'], $args['type']);
				}
				else
					throw new \Exception('Invalid action.', 3);
			break;

			case \Settlers\Constants::PLAYER_ROLL:
				$dice = $this->rollDice();
				$roll = array_sum($dice);

				// Enforce 7-roll rule
				if($roll == 7) 
					$sm->apply('roll_7');
				else
					$sm->apply('roll');
			break;

			case \Settlers\Constants::PLAYER_BUY_DEVEL:
				if($this->canAfford($player, \Settlers\Constants::BUILD_DEVEL &&
					!$this->isDevelDeckEmpty()))
					$this->drawDevel($player);
				else
					throw new \Exception('Invalid action.', 3);
			break;

			case \Settlers\Constants::PLAYER_PLAY_DEVEL:
				if($player->getDevelCardsCount($args['type']) > 0) {
					$this->playDevel($player, $args['type']);

					if($args['type'] == \Settlers\Constants::DEVEL_KNIGHT) {
						// If we played a devel during an atypical state,
						// then it has to be a knight during preroll.
						if(!$current_state->can('devel_playable')) {
							$sm->apply('play_preroll_knight');
						}
						else {
							$sm->apply('play_postroll_knight');
						}
					}
				}
				else
					throw new \Exception('Invalid action.', 3);
			break;

			case \Settlers\Constants::PLAYER_MOVE_BARON:
				$this->map->placeBaron($args['hex']);
				if($current_state->can('place_baron'))
					$sm->apply('place_baron');
				else
					$sm->apply('place_preroll_baron');
			break;

			case \Settlers\Constants::PLAYER_STEAL:
				$this->playerSteal($player, $args['player']);
				if($current_state->can('steal_baron'))
					$sm->apply('steal_baron');
				else
					$sm->apply('steal_preroll_baron');
			break;

			case \Settlers\Constants::PLAYER_DISCARD_RESOURCES:
				if(empty($args['resources'])) throw new \Exception('Invalid parameter(s).', 2);

				$total_to_discard = 0;
				$total_resources = 0;

				// Check number of resources first.
				foreach($args['resources'] as $resource => $count) {
					$resource_count = $player->getResourceCount($resource);
					$total_to_discard += $count;
					$total_resources += $resource_count;

					if($resource_count < $count)
						throw new \Exception('Invalid action.', 3);
				}

				// Players under 8 resources don't have to discard.
				if($total_resources <= 7)
					throw new \Exception('Invalid action.', 3);

				// Player must discard half of their hand (rounded up).
				if($total_to_discard < ceil($total_resources / 2))
					throw new \Exception('Invalid action.', 3);

				foreach($args['resources'] as $resource => $count) {
					$player->takeResources($resource, $count);
				}
			break;

			case \Settlers\Constants::PLAYER_END_TURN:
				$this->nextPlayerTurn();
			break;

			case \Settlers\Constants::PLAYER_TRADE:
			case \Settlers\Constants::PLAYER_EXCHANGE:
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
		$this->processGameAction('finalize_map_load');
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

		$this->processGameAction('finalize_players_order');
	}

	public function nextPlayerTurn()
	{
		$sm = $this->state_machine;
		$current_state = $sm->getCurrentState();
		$this->current_turn = ($this->current_turn + 1) % count($this->players);

		// If we are back to the first player again during setup then
		// move on from that setup state.
		if($this->current_turn == 0 && $current_state->has('setup_turn')) {
			if($current_state->has('last_setup_state')) {
				$sm->apply('end_setup_round_2');
			}
			else {
				$sm->apply('end_setup_round_1');
			}
		}
		
		// We don't end turns to check win conditions while setting up.
		if($current_state->can('end_turn')) {
			$sm->apply('end_turn');
		}
	}

	public function getPlayerTurn()
	{
		if(empty($this->players_order) || empty($this->players)) throw new \Exception('Invalid action.', 3);
		$sm = $this->state_machine;
		$current_state = $sm->getCurrentState();

		if($current_state->has('players_order_reversed'))
			return $this->players_order[count($this->players_order) - 1 - $this->current_turn];
		else 
			return $this->players_order[$this->current_turn];
	}

	public function isPlayerCurrentPlayer($player)
	{
		if(empty($player)) throw new \Exception('Missing parameter.', 1);
		if(!$player instanceof \Settlers\Player) throw new \Exception('Invalid parameter.', 2);

		return spl_object_hash($this->players[$this->getPlayerTurn()]) == spl_object_hash($player);
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

			return false;
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