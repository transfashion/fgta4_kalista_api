<?php 

use Transfashion\KalistaApi\Configuration;

Configuration::Set([
	
	'DbMain' => [
		'DSN' => "mysql:host=127.0.0.1;dbname=mydb",
		'user' => "root",
		'pass' => ""
	],

	
	
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

