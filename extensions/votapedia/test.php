<?php

class A
{
	private $arr;
	
	public function __construct()
	{
		$this->arr = 10;
	}
	
	public function & getArr()
	{
		return $this->arr;
	}
	public function pr()
	{
		var_dump($this->arr);
	}
}

$a = new A();
$a->pr();
$ars =& $a->getArr();
$ars = 1;
$a->pr();

?>