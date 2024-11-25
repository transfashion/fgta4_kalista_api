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



$qiscusConfig = Configuration::Get('Qiscus');
$qiscus_url = $qiscusConfig['Url'];
$qiscus_sender = $qiscusConfig['Sender'];
$qiscus_secret = $qiscusConfig['Secret'];
$room_id = '57278907';


Qiscus::Setup($qiscus_url, $qiscus_sender, $qiscus_secret);
Qiscus::SendText($room_id, "test message");
Qiscus::Resolve($room_id);


