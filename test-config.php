<?php


$configfile = 'config-production.php';
if (getenv('DEBUG')==='true') {
	$configfile = 'config-development.php';
}


echo $configfile;
echo "\n\n";