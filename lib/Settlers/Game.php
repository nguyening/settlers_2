<?php
namespace Settlers;
class Game {
	public $room_size;
	private $map;
	private $players = array();
	private $players_order;
	private $current_turn;

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

		if(empty($params['map']) || !$params['map'] instanceof \Settlers\Map) {
			$this->map = new \Settlers\Map(array(
				'map_size' => $params['map_size']
			));
		}
		else {
			$this->map = $params['map'];
		}
		
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

	public function getPlayer($slot)
	{
		if(empty($this->players[$slot])) return null;
		return $this->players[$slot];
	}

	/**
	 * GAME LOGIC
	 */

	public function determinePlayerOrdering()
	{
		if(empty($this->players)) throw new \Exception('Invalid action.', 3);
		$this->players_order = array_keys($this->players);

		shuffle($this->players_order);
	}

	public function getPlayerTurn()
	{
		if(empty($this->players_order) || empty($this->players)) throw new \Exception('Invalid action.', 3);
		return $this->players_order[$this->current_turn];
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