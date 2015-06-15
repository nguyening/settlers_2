<?php
namespace Settlers;
class Hex {
	public $x;
	public $y;
	public $map;
	private $vertices = array();
	private $edges = array();

	public function __construct($params)
	{
		$filter_options = array(
			'options' => array(
				'max_range' => \Settlers\Constants::MAX_SIZE
			)
		);

		if(!isset($params['x']) ||
			!isset($params['y'])
			) throw new \Exception('Missing parameter(s).', 1);

		if(filter_var($params['x'], FILTER_VALIDATE_INT, $filter_options) === false ||
			filter_var($params['y'], FILTER_VALIDATE_INT, $filter_options) === false
			) throw new \Exception('Invalid parameter(s).', 2);
		
		foreach($params as $key => $value) {
			$this->$key = $value;
		}
	}

	public function getVertex($idx)
	{
		if(empty($this->vertices[$idx]))
			return null;
		return $this->vertices[$idx];
	}

	public function addVertex($idx, $vertex)
	{
		if(!(is_int($idx) && $idx >= 0 && $idx < 6) ||
			!$vertex instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter(s)', 2);
		$this->vertices[$idx] = $vertex;
	}

	public function getEdge($idx)
	{
		if(empty($this->edges[$idx]))
			return null;
		return $this->edges[$idx];
	}

	public function addEdge($idx, $edge)
	{
		if(!(is_int($idx) && $idx >= 0 && $idx < 6) ||
			!$edge instanceof \Settlers\Edge) throw new \Exception('Invalid parameter(s)', 2);
		$this->edges[$idx] = $edge;
	}
}