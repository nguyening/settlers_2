<?php
namespace Settlers;
class Edge {
	private $port;
	private $piece;
	private $vertices;	// endpoints of this edge

	public function __construct($params = array())
	{
		$this->vertices = array();
	}

	public function setPort($port)
	{
		if(empty($port)) throw new \Exception('Missing parameter.', 1);
		if(!$port instanceof \Settlers\Port) throw new \Exception('Invalid parameter.', 2);

		$this->port = $port;
	}

	public function getPort()
	{
		return $this->port;
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