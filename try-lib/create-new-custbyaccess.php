<?php 
define('__ROOT_DIR__', dirname(__DIR__));
require_once join(DIRECTORY_SEPARATOR, [__ROOT_DIR__, 'vendor', 'autoload.php']);

use Transfashion\KalistaApi\Log;
use Transfashion\KalistaApi\Customer;

// Start Library Test
function main() : void {
	echo "TEST New Customer By Access\n";
	Log::setRequest(basename(__FILE__));
	Customer::CreateNewByAccess(Customer::ACCESSTYPE_WHATSAPP, '444', 'agung');
}


// execute main
include_once join(DIRECTORY_SEPARATOR, [__DIR__, '_execute.php']);