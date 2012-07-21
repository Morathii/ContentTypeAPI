<?php
define('CT_BASE',$_SERVER['DOCUMENT_ROOT'].'ct/');
define('CT_CONTROLLERS',CT_BASE.'controllers/');
define('CT_MODELS',CT_BASE.'models/');

define('CT_DSN','mysql:dbname=content-types;host=127.0.0.1');
define('CT_DB_USER','root');
define('CT_DB_PASS','');

if(!isset($_GET['api_key']) && !isset($_POST['api_key']))
{
	renderNoApiKeyError();
}

//can we talk to the db?
$api_controller = new ApiKeyController();

//k, wtf are we doing?
$api_controller->getResults();

function __autoload($class_name)
{
	if(strpos($class_name,'Controller') > 0)
	{
		require(CT_CONTROLLERS.$class_name.'.class.php');
	}
	else
	{
		require(CT_MODELS.$class_name.'.class.php');
	}
}

function renderNoApiKeyError()
{
	die('invalid api key');
}



