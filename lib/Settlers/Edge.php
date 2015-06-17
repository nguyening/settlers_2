<?php
namespace Settlers;
class Edge {
	public $hex;
	public $piece;
	private $vertices;	// endpoints of this edge

	public function __construct($params = array())
	{
		if(empty($params['hex'])) throw new \Exception('Missing parameters.', 1);
		if(!$params['hex'] instanceof \Settlers\Hex) throw new \Exception('Invalid parameters.', 2);
		$this->hex = $params['hex'];
		$this->vertices = array();
	}

	public function __toString()
	{
		for($i = 0; $i < 6; $i++) {
			if(spl_object_hash($this->hex->getEdge($i)) == 
				spl_object_hash($this))
				return sprintf("(%d, %d, #%d) [%s==%s]", 
					$this->hex->x, $this->hex->y, $i, $this->vertices[0], $this->vertices[1]);
		}
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

	public function getPiece()
	{
		return $this->piece;
	}

	public function setPiece($piece)
	{
		if(empty($piece)) throw new \Exception('Missing parameter.', 1);
		if(!$piece instanceof \Settlers\MapPiece) throw new \Exception('Invalid parameter.', 2);
		if($piece->getType() != \Settlers\Constants::BUILD_ROAD) throw new \Exception('Invalid action.', 3);

		$this->piece = $piece;
	}
}