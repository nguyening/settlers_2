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

	public function __construct($params = array())
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

	/**
	 * MAP RELATION LOGICS
	 */

	public function getAdjacentVertices($vertex)
	{
		if(empty($vertex)) throw new \Exception('Missing parameter.', 1);
		if(!$vertex instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter.', 2);

		$neighbors = array();
		for($i = 0; $i < 3; $i++) {
			// Try all possible edges that this vertex connects to.
			if(!empty($edge = $vertex->getEdge($i))) {
				// Grab the other vertex that this edge is connected to.
				if(spl_object_hash($edge->getVertex(0)) != spl_object_hash($vertex))
					$neighbors[] = $edge->getVertex(0);
				else
					$neighbors[] = $edge->getVertex(1);
			}
		}

		return $neighbors;
	}

	public function getAdjacentEdges($edge)
	{
		if(empty($edge)) throw new \Exception('Missing parameter.', 1);
		if(!$edge instanceof \Settlers\Edge) throw new \Exception('Invalid parameter.', 2);

		$neighbors = array();
		foreach(array($edge->getVertex(0), $edge->getVertex(1))
			as $idx => $vertex) {

			// Grab all the edges from both vertices of $edge
			for($i = 0; $i < 3; $i++) {
				if(!empty($e = $vertex->getEdge($i)) &&
					spl_object_hash($e) !== spl_object_hash($edge)) {
					$neighbors[] = $e;
				}
			}
		}

		return $neighbors;
	}

	public function isAdjacentEdge($e1, $e2)
	{
		if(empty($e1) || empty($e2)) throw new \Exception('Missing parameter(s).', 1);
		if(!$e1 instanceof \Settlers\Edge ||
			!$e2 instanceof \Settlers\Edge) throw new \Exception('Invalid parameter(s).', 2);

		$v1 = $e1->getVertex(0);
		$v2 = $e1->getVertex(1);

		// Check edges connected to each vertex for $e2
		foreach(array($v1, $v2) as $idx => $vertex) {
			for($i = 0; $i < 3; $i++) {
				if(!empty($edge = $vertex->getEdge($i)) &&
					spl_object_hash($edge) == spl_object_hash($e2))
					return true;
			}
		}

		return false;
	}

	public function isAdjacentVertex($v1, $v2)
	{
		if(empty($v1) || empty($v2)) throw new \Exception('Missing parameter(s).', 1);
		if(!$v1 instanceof \Settlers\Vertex ||
			!$v2 instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter(s).', 2);

		// Check all of $v1's edges for $v2
		for($i = 0; $i < 3; $i++) {
			if(!($edge = $v1->getEdge($i)))
				break;
			
			if(spl_object_hash($edge->getVertex(0)) == spl_object_hash($v2) || 
				spl_object_hash($edge->getVertex(1)) == spl_object_hash($v2))
				return true;
		}

		return false;
	}

	public function isBoundaryEdge($edge)
	{
		if(empty($edge)) throw new \Exception('Missing parameter.', 1);
		if(!$edge instanceof \Settlers\Edge) throw new \Exception('Invalid parameter.', 2);

		$v1 = $edge->getVertex(0);
		$v2 = $edge->getVertex(1);

		// A vertex with only two edges connected to it is always a boundary vertex
		if($v1->getEdge(2) == null || $v2->getEdge(2) == null)
			return true;
		return false;
	}

	private function isBoundaryVertex($vertex)
	{
		if(empty($vertex)) throw new \Exception('Missing parameter.', 1);
		if(!$vertex instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter.', 2);

		// Boundary vertices can have 2 or 3 edges:
		// In the (2) case, either edge is a boundary edge
		// In the (3) case, the CCW edge is always a boundary edge
		return $this->isBoundaryEdge($vertex->getEdge(1));
	}

	public function isVertexOccupied($vertex)
	{
		if(empty($vertex)) throw new \Exception('Missing parameter(s).', 1);
		if(!$vertex instanceof \Settlers\Vertex) throw new \Exception('Invalid parameter(s).', 2);

		return ($vertex->getPiece() != null);
	}

	public function isEdgeOccupied($edge)
	{
		if(empty($edge)) throw new \Exception('Missing parameter(s).', 1);
		if(!$edge instanceof \Settlers\Edge) throw new \Exception('Invalid parameter(s).', 2);

		return ($edge->getPiece() != null);
	}

	public function isVertexOccupiedByPlayer($vertex, $player)
	{
		if(empty($vertex) || empty($player)) throw new \Exception('Missing parameter(s).', 1);
		if(!$vertex instanceof \Settlers\Vertex ||
			!$player instanceof \Settlers\Player) throw new \Exception('Invalid parameter(s).', 2);

		if($this->isVertexOccupied($vertex) &&
			spl_object_hash($vertex->getPiece()->getPlayer()) == spl_object_hash($player))
			return true;
		return false;
	}

	public function isEdgeOccupiedByPlayer($edge, $player)
	{
		if(empty($edge) || empty($player)) throw new \Exception('Missing parameter(s).', 1);
		if(!$edge instanceof \Settlers\Edge ||
			!$player instanceof \Settlers\Player) throw new \Exception('Invalid parameter(s).', 2);

		if($this->isEdgeOccupied($edge) &&
			spl_object_hash($edge->getPiece()->getPlayer()) == spl_object_hash($player))
			return true;
		return false;
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

	/**
	 * MAP PLACEMENT LOGICS
	 */

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
					$edge = $hex->getEdge($i);

					if($this->isBoundaryEdge($edge)) {
						$port_edges[spl_object_hash($edge)] = $edge;
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

	public function buildPiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		if($this->canBuildPiece($player, $location, $type))
			$this->placePiece($player, $location, $type);
		else
			throw new \Exception('Invalid action.', 3);
	}

	public function canBuildPiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		if($location instanceof \Settlers\Vertex) {
			if($type == \Settlers\Constants::BUILD_CITY) {
				// Cities can only be built on current settlements owned by the player
				if($this->isVertexOccupiedByPlayer($location, $player) &&
					$location->getPiece()->getType() == \Settlers\Constants::BUILD_SETTLEMENT)
					return true;

				return false;
			}
			elseif($type == \Settlers\Constants::BUILD_SETTLEMENT) {
				// A player cannot build on an occupied vertex.
				if($this->isVertexOccupied($location))
					return false;

				// All settlements must be 1 vertex away from other vertices
				$vertices = $this->getAdjacentVertices($location);
				foreach($vertices as $idx => $vertex) {
					if($this->isVertexOccupied($vertex))
						return false;
				}

				// All settlements must be connected to a road that the player owns
				foreach(array($location->getEdge(0), $location->getEdge(1), $location->getEdge(2))
					as $idx => $edge) {
					if(empty($edge)) continue;
					if($this->isEdgeOccupiedByPlayer($edge, $player)) {
						return true;
					}
				}

				return false;
			}
		}
		elseif($location instanceof \Settlers\Edge && 
			!$this->isEdgeOccupied($location) &&
			$type == \Settlers\Constants::BUILD_ROAD) {

			// Roads must be connected to other roads owned by the player
			foreach($this->getAdjacentEdges($location) as $idx => $edge) {
				if($this->isEdgeOccupiedByPlayer($edge, $player))
					return true;
			}
			// Or, roads must be connected to a settlement/city owned by the player
			foreach(array($location->getVertex(0), $location->getVertex(1))
				as $idx => $vertex) {
				if($this->isVertexOccupiedByPlayer($vertex, $player))
					return true;
			}

			return false;
		}

		return false;
	}

	private function placePiece($player, $location, $type)
	{
		if(empty($player) ||
			empty($location) || 
			!isset($type)) throw new \Exception('Missing parameter(s).', 1);
		if(!($location instanceof \Settlers\Vertex || $location instanceof \Settlers\Edge) ||
			!$player instanceof \Settlers\Player)
			throw new \Exception('Invalid parameter(s).', 2);

		if($type == \Settlers\Constants::BUILD_CITY) {
			$location->getPiece()->setType($type);
		}
		else {
			$piece = new \Settlers\MapPiece(array(
				'type' => $type,
				'player' => $player
			));
			$location->setPiece($piece);
		}
	}
}