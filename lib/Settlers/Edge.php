<?php
namespace Settlers;
class Edge {
	private $hex;
	private $vertices;	// endpoints of this edge

	public function __construct($params)
	{
		if(empty($params['hex'])) throw new \Exception('Missing parameters.', 1);
		if(!$params['hex'] instanceof \Settlers\Hex) throw new \Exception('Invalid parameters.', 2);
		$this->hex = $params['hex'];
		$this->vertices = array();
	}

	public function addVertex($idx, $vertex)
	{
		if(!(is_int($idx) && $idx >= 0 && $idx < 2) ||
			!$vertex instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter(s)', 2);
		$this->vertices[$idx] = $vertex;
	}

	public function getVertex($idx)
	{
		if(empty($this->vertices[$idx]))
			return null;
		return $this->vertices[$idx];
	}
}