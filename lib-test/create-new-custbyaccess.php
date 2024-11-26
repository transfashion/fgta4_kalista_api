<?php 
require_once join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'vendor', 'autoload.php']);
require_once join(DIRECTORY_SEPARATOR, [__DIR__, '_preparation.inc.php']);

use Transfashion\KalistaApi\Log;
use Transfashion\KalistaApi\Customer;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Configuration;




try {

	Log::setRequest(basename(__FILE__));

	// Connect Database
	$cfgkey = Configuration::GetUsedConfig(Configuration::DB_MAIN);
	$dbconfig = Configuration::Get($cfgkey);
	Database::Connect(Configuration::DB_MAIN, $dbconfig);
	Log::info('Databse Connected!');
		


	echo "TEST New Customer By Access\n";
	Customer::CreateNewByAccess(Customer::ACCESSTYPE_WHATSAPP, '444', 'agung');

} catch (Exception $ex) {
	echo "ERROR.\n";
	echo $ex->getMessage();
} finally {
	echo "\n\n\n";
}



