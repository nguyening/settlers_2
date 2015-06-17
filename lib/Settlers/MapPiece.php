<?php
namespace Settlers;
class MapPiece {
	private $type;
	private $player;

	public function __construct($params = array())
	{
		if(!isset($params['type']) || empty($params['player'])) 
			throw new \Exception('Missing parameter(s).', 1);
		if(!in_array($params['type'], array(
			\Settlers\Constants::BUILD_ROAD,
			\Settlers\Constants::BUILD_SETTLEMENT)
		) || !$params['player'] instanceof \Settlers\Player) 
			throw new \Exception('Invalid parameter(s).', 2);

		$this->type = $params['type'];
		$this->player = $params['player'];
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
}