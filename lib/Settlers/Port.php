<?php
namespace Settlers;
class Port {
	public $resource;
	private $edge;

	public function __construct($params = array())
	{
		if(empty($params['edge']) ||
			!isset($params['resource'])) throw new \Exception('Missing parameter(s).', 1);
		if(!($params['edge'] instanceof \Settlers\Edge ||
			is_int($params['resource']))) throw new \Exception('Invalid parameter(s).', 2);

		$this->edge = $params['edge'];
		$this->resource = $params['resource'];
	}
}