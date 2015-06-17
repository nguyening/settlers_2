<?php
namespace Settlers;
class MapPiece {
	private $location;
	private $type;
	private $player;

	public function __construct($params = array())
	{
		if(!isset($params['type']) || 
			empty($params['player']) || 
			empty($params['location'])) 
			throw new \Exception('Missing parameter(s).', 1);
		if(!in_array($params['type'], array(
			\Settlers\Constants::BUILD_ROAD,
			\Settlers\Constants::BUILD_SETTLEMENT)
		) || !$params['player'] instanceof \Settlers\Player) 
			throw new \Exception('Invalid parameter(s).', 2);

		if(!(($params['location'] instanceof \Settlers\Vertex && 
			 $params['type'] == \Settlers\Constants::BUILD_SETTLEMENT) ||
			($params['location'] instanceof \Settlers\Edge && 
			 $params['type'] == \Settlers\Constants::BUILD_ROAD)))
			throw new \Exception('Invalid parameter(s).', 2);

		$this->location = $params['location'];
		$this->type = $params['type'];
		$this->player = $params['player'];

		$this->player->addPiece($this);
		$this->location->setPiece($this);
	}

	public function getType()
	{
		return $this->type;
	}

	public function setType($type)
	{
		if(!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!in_array($type, array(
			\Settlers\Constants::BUILD_ROAD,
			\Settlers\Constants::BUILD_SETTLEMENT,
			\Settlers\Constants::BUILD_CITY)
		)) throw new \Exception('Invalid parameter(s).', 2);

		$this->type = $type;
	}

	public function getPlayer()
	{
		return $this->player;
	}

	public function getLocation()
	{
		return $this->location;
	}
}