<?php

class ApiKeyController extends BaseController{

	public $model_name = 'ApiKey';

	protected function init()
	{
		$this->request_method = $this->getRequestMethod();

		$api_key = $this->getApiKey();

		if(!is_null($api_key) && !empty($api_key))
		{
			define('CT_API_KEY',$api_key[0]['api_key']);
			define('CT_API_KEY_ID',$this->getApiKeyFromRequest());
			$this->generateModel($api_key[0]);
		}
		else
		{
			renderNoApiKeyError();
		}
	}

	private function getApiKey()
	{

		$bind = array(
			'api_key' => $this->getApiKeyFromRequest()
			);

		return $this->db->select('api_keys','`api_key` = :api_key AND active = 1',$bind);
	}

	private function getApiKeyFromRequest()
	{
		switch($this->getRequestMethod())
		{
			case 'GET':
				//we're returning results
				return $_GET['api_key'];
			break;

			case 'POST':
				//we're adding/updates results
				return $_POST['api_key'];
			break;
		}
	}
}