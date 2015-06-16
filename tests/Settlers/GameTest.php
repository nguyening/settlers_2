<?php
class GameTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$game = new \Settlers\Game(array());
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$game = new \Settlers\Game(array(
			'map_size' => 'a',
		));
	}

	public function testCreate()
	{
		$game = new \Settlers\Game(array(
			'map_size' => 5
		));
		$this->assertObjectHasAttribute('map', $game);

		return $game;
	}
}