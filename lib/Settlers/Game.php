<?php
namespace Settlers;
class Game {
	public $id;
	private $map;
	private $state = array();

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

		$this->map = new \Settlers\Map(array(
			'map_size' => $params['map_size']
		));
	}
}