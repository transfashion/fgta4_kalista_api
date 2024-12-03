<?php 
define('__ROOT_DIR__', dirname(__DIR__));
require_once join(DIRECTORY_SEPARATOR, [__ROOT_DIR__, 'vendor', 'autoload.php']);

use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Qiscus;
use Transfashion\KalistaApi\Log;

// Start Library Test
function main() : void {
	echo "Try Qiscus Library\n";
	Log::setRequest(basename(__FILE__));
	
	$qiscusConfig = Configuration::Get('Qiscus');
	$url = $qiscusConfig['Url'];
	$appcode = $qiscusConfig['AppCode'];
	$secret = $qiscusConfig['AppSecret'];
	$sender = $qiscusConfig['Sender'];
	$room_id = '57278907';

	Qiscus::Setup($url, $appcode, $secret, $sender);
	Qiscus::SendText($room_id, "test message");
	Qiscus::Resolve($room_id);
}

// execute main
include_once join(DIRECTORY_SEPARATOR, [__DIR__, '_execute.php']);


