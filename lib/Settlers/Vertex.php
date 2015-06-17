<?php
namespace Settlers;
class Vertex {
	private $piece;
	private $edges;		// edges that are incident on this vertex

	public function __construct($params = array())
	{
		$this->edges = array();
	}

	public function addEdge($idx, $edge)
	{
		if(!(is_int($idx) && $idx >= 0 && $idx < 3) ||
			!$edge instanceof \Settlers\Edge) throw new \Exception('Invalid parameter(s)', 2);
		$this->edges[$idx] = $edge;
	}

	public function getEdge($idx)
	{
		if(empty($this->edges[$idx]))
			return null;
		return $this->edges[$idx];
	}

	public function getPiece()
	{
		return $this->piece;
	}

	public function setPiece($piece)
	{
		if(empty($piece)) throw new \Exception('Missing parameter.', 1);
		if(!$piece instanceof \Settlers\MapPiece) throw new \Exception('Invalid parameter.', 2);
		if($piece->getType() != \Settlers\Constants::BUILD_SETTLEMENT) throw new \Exception('Invalid action.', 3);

		$this->piece = $piece;
	}
}