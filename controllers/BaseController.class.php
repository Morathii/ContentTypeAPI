<?php
require(CT_BASE.'class.db.php');

class BaseController{

	private $content_type;
	private $content_type_item;
	protected $db = '';
	public $model_name = 'N/A';
	public $model = NULL;
	protected $request_method = '';

	public function __construct()
	{
		$this->initDb();
		$this->init();
	}

	protected function init(){}

	public function generateModel($values)
	{
		if($this->model_name != 'N/A' && $values != '')
		{
			$this->model = new $this->model_name;
			foreach($values as $key=>$value)
			{
				$this->model->$key = $value;
			}
		}
	}

	public function initDb()
	{
		if(empty($this->db))
		{
			$this->db = new db(CT_DSN,CT_DB_USER,CT_DB_PASS);
		}
	}

	protected function getRequestMethod()
	{
		if($this->request_method == '')
		{
			switch($_SERVER['REQUEST_METHOD'])
			{
				case 'GET':
					$this->request_method = 'GET';
				break;

				case 'POST':
					$this->request_method = 'POST';
				break;
			}
		}

		return $this->request_method;
	}

	public function setContentType()
	{
		switch($this->getRequestMethod())
		{
			case 'GET':
				if(isset($_GET['content_type']) && $_GET['content_type'] != '')
				{
					$this->content_type = $_GET['content_type'];
				}
			break;

			case 'POST':
				if(isset($_POST['content_type']) && $_POST['content_type'] != '')
				{
					$this->content_type = $_POST['content_type'];
				}
			break;

		}
		return $this;
	}

	public function getContentType()
	{
		return $this->content_type;
	}

	public function setContentTypeItem()
	{
		switch($this->getRequestMethod())
		{
			case 'GET':
			if(isset($_GET['item']) && $_GET['item'] != '')
			{
				$this->content_type_item = $_GET['item'];
			}
			break;

			case 'POST':
			if(isset($_POST['item']) && $_POST['item'] != '')
			{
				$this->content_type_item = $_POST['item'];
			}
			break;
		}

		return $this;
	}

	public function getContentTypeItem()
	{
		return $this->content_type_item;
	}

	public function getResults()
	{
		switch($this->getRequestMethod())
		{
			case 'GET':
				//we're returning results
				$this->returnValidMethodRequest();
			break;

			case 'POST':
				//we're adding/updates results
				$this->returnValidMethodRequest();
			break;

			default:
				echo 'unsupported request type';
			break;
		}
	}


	//should we just throw all of this in the base controller, or what, 
	//we can't classes between the two controllers
	//if we moved to a Model Operations View Event setup could this take care of a lot of this.
	//the event is what has come in and the operations is what it pulls back?
	protected function returnValidMethodRequest()
	{
		$this->setContentType()
			->setContentTypeItem();


		if(!is_null($this->getContentType()) && !is_null($this->getContentTypeItem()))
		{
			$controller = new ContentTypeItemController(CT_DSN,CT_DB_USER,CT_DB_PASS);

			$controller->setContentType($this->getContentType());
			$controller->setContentTypeItem($this->getContentTypeItem());
			$controller->getContentTypeItemStructure();
		}
		elseif(!is_null($this->getContentType()) && is_null($this->getContentTypeItem()))
		{
			//getting content type structure
			$content_type_controller = new ContentTypeController(CT_DSN,CT_DB_USER,CT_DB_PASS);

			$content_type_controller->setContentType($this->getContentType());
			$content_type_controller->setContentTypeItem($this->getContentTypeItem());
			$content_type_controller->getContentTypeStructure();
		}
		else
		{
			echo '...erm..... ?';
		}
	}

	protected function sendResults($results)
	{
		if(empty($results))
		{
			//set header to fail

		}


		echo json_encode($results);
	}


}