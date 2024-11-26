<?php 


use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Log;
use Transfashion\KalistaApi\Database;

Configuration::setRootDir(__DIR__);

$configfile = 'config-production.php';
if (getenv('DEBUG')==='true') {
	$configfile = 'config-development.php';
}

$sp = DIRECTORY_SEPARATOR;
$configpath = join(DIRECTORY_SEPARATOR, [__DIR__, '..', $configfile]);
if (!is_file($configpath)) {
	throw new \Exception("$configpath not found", 500);
}

require_once $configpath;

$logoutput = Configuration::Get('Log.Output') ?? join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'output', 'log.txt']); // default output/log.txt
$logmaxsize = Configuration::Get('Log.MaxSize') ?? 10485760; // default 10M
Log::setOutput($logoutput, $logmaxsize);



