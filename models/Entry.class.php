<?php

class Entry extends BaseModel{

	public $content_type_item_id = 0;
	public $content_type_id = 0;
	public $value = '';
	public $fields = 0;

	public function __construct($data = array())
	{
		if(!empty($data))
		{
			$this->content_type_item_id = $data['item_id'];
			$this->content_type_id = $data['content_type_id'];
			$this->value = json_decode($data['value']);
		}
	}


}
