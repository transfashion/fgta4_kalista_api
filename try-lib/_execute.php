<?php
require_once join(DIRECTORY_SEPARATOR, [__ROOT_DIR__, 'config-development.php']);

use AgungDhewe\Cli\color;

use Transfashion\KalistaApi\Log;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Configuration;


try {
	// Preparation
	Configuration::setRootDir(__ROOT_DIR__);
	Configuration::SetLogger();
	

	// Connect Database
	$cfgkey = Configuration::GetUsedConfig(Configuration::DB_MAIN);
	$dbconfig = Configuration::Get($cfgkey);
	Database::Connect(Configuration::DB_MAIN, $dbconfig);
	Log::info('Databse Connected!');
	main();
} catch (Exception $ex) {
	echo color::FG_BOLD_RED . "ERROR." . color::RESET . "\n";
	echo $ex->getMessage();
} finally {
	echo "\n\n\n";
}



