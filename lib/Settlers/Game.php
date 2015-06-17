<?php
namespace Settlers;
class Game {
	public $room_size;
	private $map;
	private $players = array();

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

	public function checkAffordBuild($player, $build_type)
	{
		foreach(\Settlers\Constants::COST_BUILD[$build_type] as $resource => $amount) {
			if($player->getResourceCount($resource) < $amount) return false;
		}
		return true;
	}
}