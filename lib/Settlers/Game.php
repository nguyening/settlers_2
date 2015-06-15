<?php

class Game {
	private $id;
	private $state = array();

	public function __construct($params)
	{
		foreach($params as $param) {
			$rule = $param['name'];
			$value = json_decode($param['value']);
			$this->state[$rule] = $value;
	}

	public function FunctionName($value='')
	{
		# code...
	}
}