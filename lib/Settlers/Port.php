<?php
namespace Settlers;
class Port {
	public $resource;
	private $edge;

	public function __construct($params = array())
	{
		if(!isset($params['resource'])) throw new \Exception('Missing parameter(s).', 1);
		if(!is_int($params['resource'])) throw new \Exception('Invalid parameter(s).', 2);

		$this->resource = $params['resource'];
	}

	public function getResourceType()
	{
		return $this->resource;
	}
}