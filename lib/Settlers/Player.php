<?php
namespace Settlers;
class Player {
	private $user;
	private $resources = array(
		\Settlers\Constants::RESOURCE_ORE => 0,
		\Settlers\Constants::RESOURCE_BRICK => 0,
		\Settlers\Constants::RESOURCE_WHEAT => 0,
		\Settlers\Constants::RESOURCE_SHEEP => 0,
		\Settlers\Constants::RESOURCE_WOOD => 0,
		\Settlers\Constants::RESOURCE_ANY => 0
	);
	private $active_devel_cards = array(
		\Settlers\Constants::DEVEL_KNIGHT => 0,
		\Settlers\Constants::DEVEL_MONOPOLY => 0,
		\Settlers\Constants::DEVEL_ROAD_BUILDING => 0,
		\Settlers\Constants::DEVEL_YEAR_OF_PLENTY => 0,
		\Settlers\Constants::DEVEL_VICTORY_POINT => 0
	);
	private $purchased_devel_cards = array(
		\Settlers\Constants::DEVEL_KNIGHT => 0,
		\Settlers\Constants::DEVEL_MONOPOLY => 0,
		\Settlers\Constants::DEVEL_ROAD_BUILDING => 0,
		\Settlers\Constants::DEVEL_YEAR_OF_PLENTY => 0,
		\Settlers\Constants::DEVEL_VICTORY_POINT => 0
	);
	private $pieces = array();

	public function __construct($params = array())
	{
		if(empty($params['user'])) throw new \Exception('Missing parameter(s).', 1);
		// if(!$params['user'] instanceof User) throw new \Exception('Invalid parameter(s).', 2);

		$this->user = $params['user'];
	}

	public function addPiece($piece)
	{
		if(empty($piece)) throw new \Exception('Missing parameter.', 1);
		if(!$piece instanceof \Settlers\MapPiece) throw new \Exception('Invalid parameter.', 2);

		if(empty($this->pieces[$piece->getType()])) $this->pieces[$piece->getType()] = array();
		$this->pieces[$piece->getType()][] = $piece;
	}

	public function getPieces()
	{
		return $this->pieces;
	}

	public function getResourceCount($resource)
	{
		if(!isset($this->resources[$resource])) throw new \Exception('Invalid parameter(s).', 2);
		return $this->resources[$resource];
	}

	public function takeResources($resource, $count)
	{
		if(!isset($this->resources[$resource])) throw new \Exception('Invalid parameter(s).', 2);
		if($this->resources[$resource] < $count) throw new \Exception('Invalid action.', 3);
		$this->resources[$resource] -= $count;
	}

	public function addResources($resource, $count)
	{
		if(!isset($this->resources[$resource])) throw new \Exception('Invalid parameter(s).', 2);
		$this->resources[$resource] += $count;
	}

	public function getDevelCardsCount($devel, $active = true)
	{
		if(!isset($this->active_devel_cards[$devel])) throw new \Exception('Invalid parameter.', 2);

		if($active) $devel_cards = $this->active_devel_cards;
		else $devel_cards = $this->purchased_devel_cards;

		return $devel_cards[$devel];
	}

	public function takeDevelCards($devel, $count, $active = true)
	{
		if(!isset($this->active_devel_cards[$devel])) throw new \Exception('Invalid parameter.', 2);

		if($active) {
			if($this->active_devel_cards[$devel] < $count) 
				throw new \Exception('Invalid action.', 3);

			$this->active_devel_cards[$devel] -= $count;
		}
		else {
			if($this->purchased_devel_cards[$devel] < $count) 
				throw new \Exception('Invalid action.', 3);

			$this->purchased_devel_cards[$devel] -= $count;
		}
	}


	public function addDevelCards($devel, $count, $active = true)
	{
		if(!isset($this->active_devel_cards[$devel])) throw new \Exception('Invalid parameter.', 2);

		if($active) 
			$this->active_devel_cards[$devel] += $count;
		else 
			$this->purchased_devel_cards[$devel] += $count;
	}
}