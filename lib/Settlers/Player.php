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

	public function __construct($params = array())
	{
		if(empty($params['user'])) throw new \Exception('Missing parameter(s).', 1);
		// if(!$params['user'] instanceof User) throw new \Exception('Invalid parameter(s).', 2);

		$this->user = $params['user'];
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
}