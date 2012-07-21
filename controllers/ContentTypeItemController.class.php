<?php

class ContentTypeItemController extends BaseController{
	
	public $model_name = 'ContentTypeItem';

	protected function getAllItemsAndAllContentTypes()
	{
		$bind = array(
			'api_key' => CT_API_KEY
			);

		$sql = 'SELECT ct_types.name as content_type,ct_fields.field, ct_item.content_type_item_id as item_id, ct_item.value FROM `content_type_items` ct_item 
		LEFT JOIN `content_type_fields` ct_fields ON ct_fields.content_type_field_id = ct_item.content_type_field_id 
		LEFT JOIN `content_types` ct_types ON ct_fields.content_type_id = ct_types.content_type_id 
		LEFT JOIN `api_keys` a ON ct_types.api_key_id = a.api_key_id WHERE a.api_key = :api_key';
		return $this->db->run($sql,$bind);
	}

	protected function getAllItemsForAContentType()
	{
		$bind = array(
			'name'=>$this->getContentType(),
			'api_key' => CT_API_KEY
			);

		$sql = 'SELECT ct_types.name as content_type, ct_types.content_type_id as content_type_id, ct_item.content_type_item_id as item_id, ct_item.value 
		FROM `content_type_items` ct_item 
		LEFT JOIN `content_types` ct_types ON ct_item.content_type_id = ct_types.content_type_id 
		LEFT JOIN `api_keys` a ON ct_types.api_key_id = a.api_key_id WHERE a.api_key = :api_key 
		AND ct_types.name=:name';
		return $this->db->run($sql,$bind);

	}

	protected function getAnItemForAContentType()
	{
		$bind = array(
			'name'=>$this->getContentType(),
			'api_key' => CT_API_KEY,
			'item_id' => $this->getContentTypeItem()
			);

		$sql = 'SELECT ct_types.name as content_type,ct_fields.field,ct_item.value FROM `content_type_items` ct_item 
		LEFT JOIN `content_type_fields` ct_fields ON ct_fields.content_type_field_id = ct_item.content_type_field_id 
		LEFT JOIN `content_types` ct_types ON ct_fields.content_type_id = ct_types.content_type_id 
		LEFT JOIN `api_keys` a ON ct_types.api_key_id = a.api_key_id WHERE a.api_key = :api_key
		AND ct_types.name=:name AND ct_item.content_type_item_id = :item_id';
		return $this->db->run($sql,$bind);
	}

	public function getContentTypeItemStructure()
	{
		//getting Items or Item Structure
		switch($this->getContentTypeItem())
		{
			case "*":
			if($this->getContentType() == '*')
			{
					#	4) /content-type/api-key/*/*/
				$structure = $this->getAllItemsAndAllContentTypes();
				$this->sendResults($structure);
			}
			else
			{
					#	7) /content-type/api-key/content-type-name/*/
				$items = $this->getAllItemsForAContentType();
					//build the models, yo!
				$structure = array('error'=>'no results found');
				if(!empty($items))
				{
					$structure = $this->getStructureForItems($items);
				}
				$this->sendResults($structure);
			}
			break;

			default:
				#	9) /content-type/api-key/content-type-name/content-id/
			$structure = $this->getAnItemForAContentType();
			$this->sendResults($structure);
			break;
		}
	}

	private function getStructureForItems($items = array())
	{
		$typeController = new ContentTypeController(CT_DSN,CT_DB_USER,CT_DB_PASS);

		$entries = array();
		foreach($items as $entry)
		{
			$entry_obj = new Entry($entry);
			$fields = array();
			foreach($entry_obj->value as $key=>$value)
			{
				$field = (array) $typeController->getContentFieldByKey($key);
				$field['value'] = $value;
				$fields[] = $field;
			}
			$entry_obj->fields = $fields;
			$entries[] = $entry_obj;
		}
		return $entries;
	}
}