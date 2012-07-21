<?php

class Fields extends BaseModel{

	public $content_type_fields_id = 0;
	public $content_type_id = 0;
	public $field = '';
	public $value = '';

	public function __construct($data = array())
	{
		if(array_key_exists(0, $data) && !empty($data[0]))
		{
			$this->content_type_fields_id = $data[0]['content_type_field_id'];
			$this->content_type_id = $data[0]['content_type_id'];
			$this->field = $data[0]['field'];
		}
	}


}
