<?php

class ContentTypeController extends BaseController{

	public $model_name = 'ContentType';

	protected function getContentTypeStructureByName($name)
	{
		$bind = array(
			'api_key' => CT_API_KEY,
			'name' => $name
			);
		return $this->db->select('content_types t LEFT JOIN content_type_fields tf ON t.content_type_id = tf.content_type_id LEFT JOIN api_keys a ON t.api_key_id = a.api_key_id','a.api_key=:api_key AND t.name=:name',$bind);
	}

	protected function getAllContentTypes()
	{
		$bind = array(
			'api_key_id' => CT_API_KEY_ID
			);

		return $this->db->select('content_types','api_key_id=:api_key_id',$bind);
	}

	public function getContentFieldByKey($key=0)
	{
		if((int) $key > 0)
		{
			$bind = array(
				'api_key' => CT_API_KEY,
				'content_type_field_id' => $key
			);
			$q = "SELECT * FROM content_type_fields tf LEFT JOIN content_types t ON tf.content_type_id = t.content_type_id 
				LEFT JOIN api_keys a ON t.api_key_id = a.api_key_id WHERE a.api_key=:api_key AND a.active=1 AND tf.content_type_field_id=:content_type_field_id";
			$result = $this->db->run($q,$bind);
			$field = new Fields($result);
			return $field;
		}

		return false;
	}

	public function getEntryById($entry_id=0)
	{
		if((int) $entry_id > 0)
		{
			$bind = array(
				'api_key' => CT_API_KEY,
				'entry_id' => $entry_id
			);

			$q = "SELECT i.content_type_item_id as item_id, i.content_type_id, i.value FROM content_type_items i LEFT JOIN content_types t ON i.content_type_id = t.content_type_id 
			LEFT JOIN api_keys a ON t.api_key_id = a.api_key_id WHERE a.api_key=:api_key AND a.active=1 AND i.content_type_item_id = :entry_id";
			$result = $this->db->run($q,$bind);
			$entry = new Entry($result[0]);
			return $entry;
		}
	}

	private function addContentType()
	{
		if($this->getContentType() != '')
		{
			//get content type id
			$bind = array(
				'name' => $this->getContentType(),
				'api_key_id' => CT_API_KEY_ID
			);
		}
		return $this->db->insert('content_types',$bind);
	}

	private function addFieldToContentType($fields)
	{
		$last_insert_id = $this->db->lastInsertId();
		if((int) $last_insert_id > 0)
		{
			$results = array();
			foreach($fields as $field)
			{
				$bind = array(
					'content_type_id'=> $last_insert_id,
					'field'=> $field
				);
				$results[] = $this->db->insert('content_type_fields',$bind);
			}
			return $results;
		}
		else
		{
			return "could not find inserted id";
		}
	}

	private function editFieldsOfContentType($fields=array())
	{
		$results = array();
		
		if(!empty($fields))
		{
			foreach ($fields as $key => $field)
			{
				#update new name to content_type_field_id if the content_type_id is in their api
				//get the current one
				$result = $this->getContentFieldByKey($key);				

				if(!empty($result))
				{
					$bind = array(
						'field'=>$field
					);
					$update_query = "UPDATE `content_type_fields` SET field = :field WHERE content_type_field_id = ".$key;
					$update = $this->db->run($update_query,$bind);
					if($update)
					{
						//TODO: get the updated info from the DB and add to array to send back
						$results[] = '';
					}
				}
			}
		}
		return $results;
	}

	//should be in ContentTypeItem???
	private function addItemToContentType($items = array())
	{
		$results = array();

		$content_type_info = $this->getContentTypeStructureByName($this->getContentType());

		if(!empty($items) && !empty($content_type_info))
		{
			$json_array = array();
			foreach($items as $key=>$item)
			{
				$content_field = $this->getContentFieldByKey($key);
				if($content_field !== false)
				{
					//field is owned by api key owner, so we can add to the 
					$json_array[$key] = $item;
				}
			}

			$bind = array(
				'content_type_id'=> $content_type_info[0]['content_type_id'],
				'value'=> json_encode($json_array)
				);
			$results = $this->db->insert('content_type_items',$bind);
		}
		return $results;
	}

	//should this be in CotentTypeItem?
	private function editItemOfContentType($entry_id=0,$items = array())
	{
		//does the entry exist?
		if((int) $entry_id <= 0)
		{
			return false;
		}
		$entry = $this->getEntryById($entry_id);

		$results = array();

		$content_type_info = $this->getContentTypeStructureByName($this->getContentType());

		if(is_object($entry) && !empty($items) && !empty($content_type_info))
		{
			$json_array = array();
			foreach($items as $key=>$item)
			{
				$content_field = $this->getContentFieldByKey($key);
				if($content_field !== false)
				{
					//field is owned by api key owner, so we can add to the 
					$json_array[$key] = $item;
				}
			}

			$bind = array(
				'content_type_item_id'=> $entry_id,
				'value'=> json_encode($json_array)
				);
			$q = "UPDATE content_type_items SET value = :value WHERE content_type_item_id = :content_type_item_id";
			$results = $this->db->run($q,$bind);
		}
		return $results;		
	}

	public function getContentTypeStructure()
	{
		//switch between request type
		switch($this->getRequestMethod())
		{
			case 'GET':
				$this->getContentTypeByGet();
			break;

			case 'POST':
				$this->getContentTypeByPost();
			break;
		}
	}

	protected function getContentTypeByGet()
	{
		switch($this->getContentType())
		{
			case '*':
				#	3) /content-type/api-key/*/
				$structure = $this->getAllContentTypes();
				$this->sendResults($structure);
			break;

			default:
				#	2) /content-type/api-key/content-type-name/
				$structure = $this->getContentTypeStructureByName($this->getContentType());
				$this->sendResults($structure);
			break;
		}
	}

	protected function getContentTypeByPost()
	{
		$fields_added = '';
		#	1) /content-type/api-key/content-type-name/
		//does this content type exist?
		if($this->getContentTypeStructureByName($this->getContentType()) && (isset($_POST['fields']) && $_POST['fields'] != ''))
		{
			$content_type_fields = $this->editFieldsOfContentType(json_decode($_POST['fields']));
			$this->sendResults($content_type_fields);
		}
		elseif($this->getContentTypeStructureByName($this->getContentType()) && (isset($_POST['entry_id']) && $_POST['entry_id'] != '') && (isset($_POST['items']) && $_POST['items'] != ''))
		{
			$content_items = $this->editItemOfContentType($_POST['entry_id'],json_decode($_POST['items']));
			$this->sendResults($content_items);
		}
		elseif($this->getContentTypeStructureByName($this->getContentType()) && (isset($_POST['items']) && $_POST['items'] != ''))
		{
			$content_items = $this->addItemToContentType(json_decode($_POST['items']));
			$this->sendResults($content_items);
		}		
		else
		{
			$content_type = $this->addContentType();
			$this->sendResults($content_type);
			if(isset($_POST['fields']) && $_POST['fields'] != '')
			{
				$fields_added = $this->addFieldToContentType(json_decode($_POST['fields']));
				if($fields_added != '')
				{
					$this->sendResults($fields_added);
				}
			}
		}
	}
}