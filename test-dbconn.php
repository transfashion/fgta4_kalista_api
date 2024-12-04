<?php declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';

use AgungDhewe\Cli\color;


use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;





try {
	$configfile = 'config-production.php';
	if (getenv('DEBUG')==='true') {
		$configfile = 'config-development.php';
	}

	echo "Config used: " . $configfile . "\n";
	$configpath = join(DIRECTORY_SEPARATOR, [__DIR__, $configfile]);
	if (!is_file($configpath)) {
		throw new \Exception("$configpath not found", 500);
	}

	Configuration::SetRootDir(__DIR__);
	echo "loading config: " . $configpath . "\n";
	require_once $configpath;	


	echo "\n";
	$test = [Configuration::DB_RPT, Configuration::DB_MAIN];
	foreach ($test as $DB_KEY) {
		echo "Test connection to: ". color::FG_BOLD_WHITE .  $DB_KEY . color::RESET ."\n";
		$cfgkey = Configuration::GetUsedConfig($DB_KEY);
		$dbconfig = Configuration::Get($cfgkey);
		echo "DSN: " . $dbconfig['DSN'] . "\n";
		echo "user: " . $dbconfig['user'] . "\n";

		Database::Connect($DB_KEY, $dbconfig);
		echo color::FG_BOLD_GREEN . "Databse Connected!".  color::RESET . "\n";
		echo "\n";
	}


} catch (\Exception $ex) {
	echo color::FG_BOLD_RED . "ERROR" . color::RESET . "\n";
	echo $ex->getMessage();
} finally {
	echo "\n\n";
}
