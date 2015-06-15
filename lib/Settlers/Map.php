<?php
namespace Settlers;
class Map {
	public $map_size;
	private $hexes;

	const HEX_DIR_E = 0;
	const HEX_DIR_SE = 1;
	const HEX_DIR_SW = 2;
	const HEX_DIR_W = 3;
	const HEX_DIR_NW = 4;
	const HEX_DIR_NE = 5;

	public function __construct($params)
	{
		$filter_options = array(
			'options' => array(
				'min_range' => 0,
				'max_range' => \Settlers\Constants::MAX_SIZE
			)
		);
		if(empty($params['map_size'])) throw new \Exception('Missing parameter(s).', 1);

		if(filter_var($params['map_size'], FILTER_VALIDATE_INT, $filter_options) === false)
			throw new \Exception('Invalid parameter(s).', 2);
		
		// foreach($params as $key => $value) {
		// 	$this->$key = $value;
		// }

		$this->map_size = $params['map_size'];

		$this->constructHexes();
		$this->constructNetwork();
	}

	private function constructHexes()
	{
		// Using a hashmap to map coordinates to hexes,
		// This helps to save space as opposed to using an array.
		$this->hexes = array();

		for($y = -1 * $this->map_size; $y <= $this->map_size; $y++) {
			for($x = -1 * $this->map_size; $x <= $this->map_size; $x++) {
				if(empty($this->hexes[$y])) $this->hexes[$y][] = array();

				$this->hexes[$y][$x] = new \Settlers\Hex(array(
					'x' => $x,
					'y' => $y
				));
			}
		}
	}

	private function constructNetwork()
	{
		// Create vertices and edges on our hexes
		for($y = -1 * $this->map_size; $y <= $this->map_size; $y++) {
			for($x = -1 * $this->map_size; $x <= $this->map_size; $x++) {
				$this->buildNetwork($this->hexes[$y][$x]);
			}
		}
		
		for($y = -1 * $this->map_size; $y <= $this->map_size; $y++) {
			for($x = -1 * $this->map_size; $x <= $this->map_size; $x++) {
				$this->connectNetwork($this->hexes[$y][$x]);
			}
		}
	}

	private function buildNetwork($hex)
	{
		for($i = 0; $i < 6; $i++) {
			$vertex = null;
			$edge = null;

			// Check if neighbor hexes have a vertex created already
			if($this->getCcwHex($hex, $i) != null)
				$vertex = $this->getCcwHexVertex($hex, $i);
			if($vertex == null && $this->getCwHex($hex, $i) != null)
				$vertex = $this->getCwHexVertex($hex, $i);
			if($vertex == null) {
				$vertex = new \Settlers\Vertex(array(
					'hex' => $hex
				));
			}
			$hex->addVertex($i, $vertex);

			// Check if neighbor hex has an edge created already
			if($this->getCwHex($hex, $i) != null)
				$edge = $this->getCwHexEdge($hex, $i);
			if($edge == null) {
				$edge = new \Settlers\Edge(array(
					'hex' => $hex
				));
			}
			$hex->addEdge($i, $edge);
		}
	}

	private function connectNetwork($hex)
	{
		for($i = 0; $i < 6; $i ++) {
			$vertex = $hex->getVertex($i);
			$edge = $hex->getEdge($i);

			// connect vertex to edges
			$e1 = $this->getVertexCwEdge($hex, $i);
			$e2 = $this->getVertexCcwEdge($hex, $i);
			$e3 = $this->getVertexOppositeEdge($hex, $i);

			$vertex->addEdge(0, $e1);
			$vertex->addEdge(1, $e2);
			if($e3 != null) $vertex->addEdge(2, $e3); // border hexes don't have neighboring edges

			// connect edge to vertices
			$v1 = $vertex;
			$v2 = $this->getEdgeOppositeVertex($hex, $i);

			$edge->addVertex(0, $v1);
			$edge->addVertex(1, $v2);
		}
	}

	/**
	 * HEXAGONAL GRID MATHS
	 */
	private function getHex($x, $y)
	{
		if(empty($this->hexes[$y]) || empty($this->hexes[$y][$x]))
			return null;
		return $this->hexes[$y][$x];
	}

	private function getCoordinatesInDirection($x, $y, $dir)
	{
		switch($dir) {
			case Map::HEX_DIR_NE:
				return array($x + 1, $y - 1);
			break;

			case Map::HEX_DIR_E:
				return array($x + 1, $y);
			break;

			case Map::HEX_DIR_SE:
				return array($x, $y + 1);
			break;

			case Map::HEX_DIR_SW:
				return array($x - 1, $y + 1);
			break;

			case Map::HEX_DIR_W:
				return array($x - 1, $y);
			break;

			case Map::HEX_DIR_NW:
				return array($x, $y - 1);
			break;
		}
	}

	private function getHexInDirection($hex, $dir) {
		$_x = $hex->x;
		$_y = $hex->y;

		// gets $x, $y -- new coordinates in the given direction
		$coords = $this->getCoordinatesInDirection($_x, $_y, $dir);
		return call_user_func_array(array($this, 'getHex'), $coords);
	}


	// Grab the neighbor counter-clockwise from $hex's vertex at $idx
	private function getCcwHex($hex, $idx)
	{
		return $this->getHexInDirection($hex, ($idx + 3) % 6);
	}

	private function getCwHex($hex, $idx)
	{
		return $this->getHexInDirection($hex, ($idx + 4) % 6);
	}

	private function getCcwHexVertex($hex, $idx)
	{
		if($this->getCcwHex($hex, $idx) == null)
			return null;
		return $this->getCcwHex($hex, $idx)->getVertex(($idx + 2) % 6);
	}

	private function getCwHexVertex($hex, $idx)
	{
		if($this->getCwHex($hex, $idx) == null)
			return null;
		return $this->getCwHex($hex, $idx)->getVertex(($idx + 4) % 6);
	}

	private function getCwHexEdge($hex, $idx)
	{
		if($this->getCwHex($hex, $idx) == null)
			return null;
		return $this->getCwHex($hex, $idx)->getEdge(($idx + 3) % 6);
	}

	// Given a vertex index, grab the edge clockwise from it
	public function getVertexCwEdge($hex, $idx)
	{
		return $hex->getEdge($idx % 6);
	}

	public function getVertexCcwEdge($hex, $idx)
	{
		return $hex->getEdge(($idx + 5) % 6);
	}

	public function getVertexOppositeEdge($hex, $idx)
	{
		// We choose to use CW hex to grab the opposite edge, but CCW also shares this edge.
		if($this->getCwHex($hex, $idx) == null)
			return null;
		return $this->getCwHex($hex, $idx)->getEdge(($idx + 4) % 6);
	}

	public function getEdgeOppositeVertex($hex, $idx)
	{
		return $hex->getVertex(($idx + 1) % 6);
	}
}