<?php 
require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);

use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Log;
use Transfashion\KalistaApi\Qiscus;


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
Log::setRequest(basename(__FILE__));


$qiscusConfig = Configuration::Get('Qiscus');
$url = $qiscusConfig['Url'];
$appcode = $qiscusConfig['AppCode'];
$secret = $qiscusConfig['AppSecret'];
$sender = $qiscusConfig['Sender'];
$room_id = '57278907';


try {
	Qiscus::Setup($url, $appcode, $secret, $sender);
	Qiscus::SendText($room_id, "test message");
	Qiscus::Resolve($room_id);
	

} catch (Exception $ex) {
	echo "ERROR.\n";
	echo $ex->getMessage();
} finally {
	echo "\n\n\n";
}



