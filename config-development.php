<?php 

use Transfashion\KalistaApi\Configuration;

Configuration::Set([
	
	'DbMain' => [
		'DSN' => "mysql:host=172.18.20.249;dbname=tfidblocal",
		'user' => "root",
		'pass' => "rahasia123!"
	],

	'Qiscus' => [
		'Url' => '',
		'Sender' => '',
		'Secret' => ''
	]
	
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

