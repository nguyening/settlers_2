<?php
class MapAssignmentsTest extends PHPUnit_Framework_TestCase {
	protected $map;
	protected $map_reflection;

	public function setUp()
	{
		$map = new \Settlers\Map(array(
			'map_size' => 2
		));
		$map_reflection = new ReflectionClass('\Settlers\Map');

		$this->map = $map;
		$this->map_reflection = $map_reflection;
	}

	public function testTerrain()
	{
		$this->map->shuffleTerrain(\Settlers\Constants::TERRAIN_DISTRIBUTION);

		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($this->map);
		$terrain_counts = array();

		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				$this->assertNotNull($hex->getTerrain());
				if(empty($terrain_counts[$hex->getTerrain()])) 
					$terrain_counts[$hex->getTerrain()] = 0;

				$terrain_counts[$hex->getTerrain()] += 1;
			}
		}

		unset($terrain_counts[\Settlers\Constants::TERRAIN_DESERT]);
		$this->assertEquals($terrain_counts, \Settlers\Constants::TERRAIN_DISTRIBUTION);
	}

	public function testChits()
	{
		$this->map->shuffleChits(\Settlers\Constants::CHIT_DISTRIBUTION);

		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($this->map);
		$chit_counts = array();

		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				$this->assertNotNull($hex->getChit());
				if(empty($chit_counts[$hex->getChit()])) 
					$chit_counts[$hex->getChit()] = 0;

				$chit_counts[$hex->getChit()] += 1;
			}
		}

		unset($chit_counts[7]);
		$this->assertEquals($chit_counts, \Settlers\Constants::CHIT_DISTRIBUTION);
	}

	public function testMapChitTotal()
	{
		$this->map->shuffleChits(\Settlers\Constants::CHIT_DISTRIBUTION);

		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($this->map);

		foreach(\Settlers\Constants::CHIT_DISTRIBUTION as $roll => $count) {
			$this->assertCount(
				$count,
				$this->map->getProducingHexes($roll)
			);
		}
	}

	public function testNoPorts()
	{
		$map = $this->map;
		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$map->shufflePorts(array());

		$ports = array();
		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				for($i = 0; $i < 6; $i++) {
					$edge = $hex->getEdge($i);
					$this->assertNull($edge->getPort());
				}
			}
		}
		
	}

	public function testPorts()
	{
		$map = $this->map;
		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($map);

		$isBoundaryEdge = $this->map_reflection->getMethod('isBoundaryEdge');
		$isBoundaryEdge->setAccessible(true);

		$map->shufflePorts(\Settlers\Constants::PORT_DISTRIBUTION);

		$ports = array();
		$port_dist_idx = 0;
		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				for($i = 0; $i < 6; $i++) {
					$edge = $hex->getEdge($i);
					if($isBoundaryEdge->invokeArgs($map, array($edge))) {
						if($map->isEdgePort($edge)) {
							$this->assertTrue($map->isEdgeResourcePort(
								$edge, \Settlers\Constants::PORT_DISTRIBUTION[$port_dist_idx]
							));
						}
						else {
							$this->assertEquals(-1, \Settlers\Constants::PORT_DISTRIBUTION[$port_dist_idx]);
						}

						$port_dist_idx++;
					}
					else {
						$this->assertFalse($map->isEdgePort($edge));
					}
				}
			}
		}
	}

	public function testAssignments()
	{
		$this->map->constructAssignments(
			\Settlers\Constants::TERRAIN_DISTRIBUTION,
			\Settlers\Constants::CHIT_DISTRIBUTION,
			\Settlers\Constants::PORT_DISTRIBUTION
		);

		$prop = $this->map_reflection->getProperty('hexes');
		$prop->setAccessible(true);
		$hexes = $prop->getValue($this->map);
		
		foreach($hexes as $r => $row) {
			foreach($row as $c => $hex) {
				if($hex->getTerrain() == \Settlers\Constants::TERRAIN_DESERT) {
					$this->assertEquals(\Settlers\Constants::CHIT_DESERT, $hex->getChit());
				}

				$this->assertNotNull($hex->getTerrain());
				$this->assertNotNull($hex->getChit());
			}
		}
	}
}