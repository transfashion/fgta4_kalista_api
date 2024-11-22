<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Session;

// script ini hanya dijalankan di web server
if (php_sapi_name() === 'cli') {
	die("Script cannot be executed directly from CLI\n\n");
}

define('HTTP_ERROR_LIST', [
	'400' => ['400', 'Bad Request'],
	'401' => ['401', 'Unauthorized'],
	'403' => ['403', 'Forbidden'],
	'405' => ['405', 'Method Not Allowed'],
	'404' => ['404', 'Not Found'],
	'500' => ['500', 'Internal Error'],
]);

$result = [];
$errCode = 0;
$errMessage = '';

ob_start();

try {

	$configfile = 'config-production.php';
	if (getenv('DEBUG')==='true') {
		$configfile = 'config-development.php';
	}

	$sp = DIRECTORY_SEPARATOR;
	$configpath = join(DIRECTORY_SEPARATOR, [__DIR__, $configfile]);
	if (!is_file($configpath)) {
		throw new \Exception("$configpath not found", 500);
	}

	require_once $configpath;

	$allowedapp_data_path = join(DIRECTORY_SEPARATOR, [__DIR__, 'allowed-apps.php']);
	if (!is_file($allowedapp_data_path)) {
		throw new \Exception("$allowedapp_data_path not found", 500);
	}
	require_once $allowedapp_data_path;

	Configuration::setRootDir(__DIR__);

	$apireq = array_key_exists('apireq', $_GET) ? trim($_GET['apireq'], '/') : null;
	if ($apireq==null) {
		throw new \Exception("Invalid Request", 403);
	}

	$classname = str_replace("/", "\\", (dirname($apireq)));
	$functionname = basename($apireq);

	if (!class_exists($classname)) {
		throw new \Exception("Class $classname not found", 404);
	}

	if (!is_subclass_of($classname, Api::class)) {
		throw new \Exception("Class $classname is not ApiClass", 400);
	}

	$api = new $classname;



	// cek apakah header cocok
	$headers = getallheaders();
	$app_id =  array_key_exists('App-Id', $headers) ? $headers['App-Id'] : "";
	$app_secret =  array_key_exists('App-Secret', $headers) ? $headers['App-Secret'] : "";

	if (!Configuration::isValidApp($app_id, $app_secret)) {
		throw new \Exception("Not Allowed invalid applications", 403);
	}


	// data yang dikirim melalui POST
	$jsonData = file_get_contents('php://input');


	// verify request
	$api->VerifyRequest($functionname, $jsonData, $headers);


	$request = json_decode($jsonData, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		throw new \Exception("Invalid request data: " . json_last_error_msg(), 400);
	}

	if (!array_key_exists('request', $request)) {
		throw new \Exception("'request' key data is not exist is not available in posted json", 400);
	}
	$receiveParameters = $request['request'];


	// get executed method information
	$refl = new \ReflectionMethod($classname, $functionname);
	$docComment = $refl->getDocComment();
	if (empty($docComment)) {
		throw new \Exception("$classname::$functionname is not api method ", 400);
	}
	if (strpos($docComment, '@ApiMethod') == false) {
		throw new \Exception("$classname::$functionname is not api method ", 400);
	}

	$funcparams = $refl->getParameters();
	$executeParameters = [];
	foreach ($funcparams as $param) {
		$paramname = $param->getName();
		if (!array_key_exists($paramname, $receiveParameters)) { // cek apakah $paramname dikirim dari POST $receiveParameters
			throw new \Exception("parameter $paramname tidak ditemukan di data yang dikirimkan", 400);
		}
		$executeParameters[] = $receiveParameters[$paramname];
	}

	$result = $api->$functionname(...array_values($executeParameters));

	header("HTTP/1.1 200 OK");
} catch (\Exception $ex) {
	$errCode = $ex->getCode();
	$errMessage = $ex->getMessage();
	$errHeaderMessage = "Internal Error";

	if (array_key_exists($errCode, HTTP_ERROR_LIST)) {
		$errHeaderMessage = HTTP_ERROR_LIST[$errCode][1];
	} else {
		$errCode = 500;
	}

	header("HTTP/1.1 $errCode $errHeaderMessage");
} finally {
	$res = json_encode([
		'code' => $errCode,
		'errormessage' => $errMessage,
		'response' => $result
	]);
	$response = json_encode($res);

	header('Content-Type: application/json');
	$output = ob_get_contents();
	ob_end_clean();

	if (!empty($output)) {
		echo $output;
	} else {
		echo stripslashes($response);
	}
	echo "\n\n";
}


