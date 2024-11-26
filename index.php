<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Log;

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

	Configuration::SetRootDir(__DIR__);
	require_once $configpath;

	$logoutput = Configuration::Get('Log.Output') ?? join(DIRECTORY_SEPARATOR, [__DIR__, 'output', 'log.txt']); // default output/log.txt
	$logmaxsize = Configuration::Get('Log.MaxSize') ?? 10485760; // default 10M
	Log::setOutput($logoutput, $logmaxsize);

	$allowedapp_data_path = join(DIRECTORY_SEPARATOR, [__DIR__, 'allowed-apps.php']);
	if (!is_file($allowedapp_data_path)) {
		throw new \Exception("$allowedapp_data_path not found", 500);
	}
	require_once $allowedapp_data_path;

	

	$apireq = array_key_exists('apireq', $_GET) ? trim($_GET['apireq'], '/') : null;
	if ($apireq==null) {
		throw new \Exception("Invalid Request", 403);
	}
	Log::setRequest($apireq);


	$classname = str_replace("/", "\\", (dirname($apireq)));
	$functionname = basename($apireq);

	if (!class_exists($classname)) {
		$errmsg = Log::access("Class $classname not found");
		throw new \Exception($errmsg , 404);
	}

	if (!is_subclass_of($classname, Api::class)) {
		$errmsg = Log::access("Class $classname is not ApiClass");
		throw new \Exception($errmsg, 400);
	}

	$api = new $classname;

	

	// cek apakah header cocok
	$headers = getallheaders();
	$app_id =  array_key_exists('App-Id', $headers) ? $headers['App-Id'] : "";
	$app_secret =  array_key_exists('App-Secret', $headers) ? $headers['App-Secret'] : "";

	$api->VerifyApplication($app_id, $app_secret);


	// data yang dikirim melalui POST
	$jsonData = file_get_contents('php://input');


	// verify request
	$api->VerifyRequest($functionname, $jsonData, $headers);



	$request = json_decode($jsonData, true);
	if (json_last_error() !== JSON_ERROR_NONE) {
		$errmsg = Log::access("Invalid request data: " . json_last_error_msg());
		throw new \Exception($errmsg, 400);
	}

	if (!array_key_exists('request', $request)) {
		$errmsg = Log::access("'request' key data is not exist is not available in posted json");
		throw new \Exception($errmsg, 400);
	}
	$receiveParameters = $request['request'];


	// get executed method information
	if (!method_exists($classname, $functionname)) {
		$errmsg = Log::access("Method '$functionname' is not found in '$classname'");
		throw new \Exception($errmsg, 400);
	};

	$refl = new \ReflectionMethod($classname, $functionname);
	$docComment = $refl->getDocComment();
	if (empty($docComment)) {
		$errmsg = Log::access("$classname::$functionname is not api method ");
		throw new \Exception($errmsg, 400);
	}
	if (strpos($docComment, '@ApiMethod') == false) {
		$errmsg = Log::access("$classname::$functionname is not api method ");
		throw new \Exception($errmsg, 400);
	}

	$funcparams = $refl->getParameters();
	$executeParameters = [];
	foreach ($funcparams as $param) {
		$paramname = $param->getName();
		$type = $param->getType();
		$isOptional = $param->isOptional();
		if (!array_key_exists($paramname, $receiveParameters)) { // cek apakah $paramname dikirim dari POST $receiveParameters
			$errmsg = Log::access("parameter $paramname tidak ditemukan di data yang dikirimkan");
			$serializedparam = print_r($receiveParameters, true);
			Log::debug($serializedparam);
			throw new \Exception($errmsg, 400);
		} else if (!$isOptional && $receiveParameters[$paramname]==null) { // cek apakah datanya null
			$errmsg = Log::access("parameter $paramname tidak boleh null");
			throw new \Exception($errmsg, 400);
		} else if (getType($receiveParameters[$paramname])!=$type) { // cek apakah tipe data sesuai,
			$errmsg = Log::access("type parameter $paramname tidak sesuai antara yang dikirim dengan yang didefinisikan di api");
			throw new \Exception($errmsg, 400);
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
	$res = [
		'code' => $errCode,
		'errormessage' => $errMessage,
		'response' => $result
	];
	$response = json_encode($res);
	$output = ob_get_contents();
	ob_end_clean();

	if (!empty($output)) {
		echo $output;
		echo "\n\n";
	} else {
		header('Content-Type: application/json');
		echo $response;
	}
}


