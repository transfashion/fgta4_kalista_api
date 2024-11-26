<?php 

use Transfashion\KalistaApi\Configuration;

Configuration::Setup([
	
	'DbMain' => [
		'DSN' => "mysql:host=172.18.20.249;dbname=tfidblocal",
		'user' => "root",
		'pass' => "rahasia123!"
	],

	'Qiscus' => [
		'Url' => 'https://omnichannel.qiscus.com',
		'AppCode' => 'zear-ekayinjcwao90mmn',
		'AppSecret' => 'fb59bccf523e8974f207d51f974cb3ba',
		'Sender' => 'zear-ekayinjcwao90mmn_admin@qismo.com',
	]
	
	
]);

Configuration::UseConfig([
	Configuration::DB_MAIN => 'DbMain',
]);

