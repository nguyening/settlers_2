<?php
namespace Settlers;
class State implements \Finite\StatefulInterface {
	private $state;

	public function getFiniteState()
	{
		return $this->state;
	}

	public function setFiniteState($state)
	{
		$this->state = $state;
	}
}