<?php
class PortTest extends PHPUnit_Framework_TestCase {
	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	1
	 */
	public function testCreateMissingParams()
	{
		$port = new \Settlers\Port();
	}

	/**
	 * @expectedException		Exception
	 * @expectedExceptionCode	2
	 */
	public function testCreateInvalidParams()
	{
		$port = new \Settlers\Port(array('resource' => "a"));
	}

	public function testCreateMock()
	{
		$edge = $this->getMockBuilder('\Settlers\Edge')
			->disableOriginalConstructor()
			->getMock();

		$port = new \Settlers\Port(array(
			'resource' => \Settlers\Constants::RESOURCE_WOOD
		));
		return $port;
	}

}