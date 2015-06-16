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

	public function testNoPorts()
	{
		$this->map->shufflePorts(array());
		$prop = $this->map_reflection->getProperty('ports');
		$prop->setAccessible(true);
		
		$this->assertCount(0, $prop->getValue($this->map));
	}

	public function testPorts()
	{
		$this->map->shufflePorts(\Settlers\Constants::PORT_DISTRIBUTION);

		$prop = $this->map_reflection->getProperty('ports');
		$prop->setAccessible(true);
		$ports = $prop->getValue($this->map);
		
		$num_ports = count(array_filter(\Settlers\Constants::PORT_DISTRIBUTION, function($item) {
			return $item >= 0;
		}));

		$this->assertCount($num_ports, $ports);
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