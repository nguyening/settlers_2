<?php
namespace Settlers;
class Map {
	public $map_size;
	private $hexes;
	private $ports;
	private $baron;

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

	public function toConsole($params = array())
	{
		$options = array_merge(array(
			'chit' => true,
			'terrain' => true,
			'coords' => false
		), $params);

		$output = "\n\n";
		foreach($this->hexes as $r => $row) {
			for($i = 0; $i < abs($r); $i++)
				$output .= "\t";

			foreach($row as $c => $hex) {
				$output .= '<';
				if($options['chit']) $output .= sprintf('%d, ', $hex->getChit());
				if($options['terrain']) $output .= sprintf('%d, ', $hex->getTerrain());
				if($options['coords']) $output .= sprintf('(%d, %d)', $hex->x, $hex->y);
				$output .= '>';
			}

			$output .= "\n";
		}
		return $output;
	}

	private function constructHexes()
	{
		// Using a hashmap to map coordinates to hexes,
		// This helps to save space as opposed to using an array.
		$this->hexes = array();

		for($y = -1 * $this->map_size; $y <= $this->map_size; $y++) {
			// Mind is pooping, I'll figure out the math property to shape this later.
			for(
				$x = ($y < 0 ? -1 * ($this->map_size - abs($y)) : -1 * $this->map_size); 
				$x <= ($y > 0 ? ($this->map_size - $y) : $this->map_size); 
				$x++) {
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
		foreach($this->hexes as $r => $row) {
			foreach($row as $c => $hex) {
				$this->buildNetwork($this->hexes[$r][$c]);
			}
		}

		// Connect vertices and edges to each other for
		// pathfinding and other traversing operations
		foreach($this->hexes as $r => $row) {
			foreach($row as $c => $hex) {
				$this->connectNetwork($this->hexes[$r][$c]);
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

	public function constructAssignments($terrain_dist, $chit_dist, $port_dist)
	{
		$this->shuffleTerrain($terrain_dist);
		$this->shuffleChits($chit_dist);
		$this->shufflePorts($port_dist);
	}

	// Shuffles terrain on the map, default terrain tile is desert
	public function shuffleTerrain($terrain_dist)
	{
		foreach($this->hexes as $r => $row) {
			foreach($row as $c => $hex) {
				$num_terrain_tiles = array_reduce($terrain_dist, function($carry, $item) { 
					$carry += $item;
					return $carry;
				});

				$t = rand(0, $num_terrain_tiles);
				$num = 0;
				foreach($terrain_dist as $terrain => $freq) {
					if($freq == 0) continue;
					$num += $freq;

					if($num >= $t) {
						$hex->setTerrain($terrain);
						$terrain_dist[$terrain]--;
						break;
					}
				}

				// Default tile is desert if we run out
				if($hex->getTerrain() == null) 
					$hex->setTerrain(\Settlers\Constants::TERRAIN_DESERT);
			}
		}
	}
	
	// Shuffles chits over the map, default chit is 7 (for desert)
	public function shuffleChits($chit_dist)
	{
		foreach($this->hexes as $r => $row) {
			foreach($row as $c => $hex) {
				if($hex->getTerrain() == \Settlers\Constants::TERRAIN_DESERT) {
					$hex->setChit(\Settlers\Constants::CHIT_DESERT);
					continue;
				}

				$num_chits = array_reduce($chit_dist, function($carry, $item) { 
					$carry += $item;
					return $carry;
				});

				$t = rand(0, $num_chits);
				$num = 0;
				foreach($chit_dist as $chit => $freq) {
					if($freq == 0) continue;
					$num += $freq;

					if($num >= $t) {
						$hex->setChit($chit);
						$chit_dist[$chit]--;
						break;
					}
				}

				// Default assignment is the desert chit (which nets nothing)
				if($hex->getChit() == null)
					$hex->setChit(\Settlers\Constants::CHIT_DESERT);
			}
		}		
	}

	public function shufflePorts($port_dist)
	{
		$this->ports = array();
		$port_edges = array();

		foreach($this->hexes as $r => $row) {
			foreach($row as $c => $hex) {
				for($i = 0; $i < 6; $i++) {
					// If this vertex doesn't have a third edge, it is a boundary vertex
					if($hex->getVertex($i)->getEdge(2) == null) {
						$vertex = $hex->getVertex($i);
						$e1 = $vertex->getEdge(0);
						$e2 = $vertex->getEdge(1);

						// Grab both edges that the vertex connects
						$port_edges[spl_object_hash($e1)] = $e1;
						$port_edges[spl_object_hash($e2)] = $e2;
					}
				}
			}
		}

		$i = 0;
		foreach($port_edges as $hash => $edge) {
			// Stop making ports if we run out in the distribution
			if(!isset($port_dist[$i])) break;

			$resource = $port_dist[$i];
			if($resource >= 0) {
				$this->ports[] = new \Settlers\Port(array(
					'edge' => $edge,
					'resource' => $resource
				));
			}
			$i++;
		}
	}

	public function placeBaron($x, $y)
	{
		$hex = $this->getHex($x, $y);
		if(empty($hex)) throw new \Exception('Invalid parameters', 2);

		$this->baron = $hex;
	}

	public function getBaron()
	{
		return $this->baron;
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