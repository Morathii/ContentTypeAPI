<?php

class BaseModel{

	public function __set($name,$value)
	{
		if(isset($this->$name))
		{
			$this->$name = $value;
		}
	}

	public function __get($name)
	{
		if(isset($this->$name))
		{
			return $this->$name;
		}
	}
}