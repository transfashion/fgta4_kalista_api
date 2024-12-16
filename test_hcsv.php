<?php
require_once __DIR__ . '/vendor/autoload.php';


use Transfashion\KalistaApi\HCsv;

$filepath = join(DIRECTORY_SEPARATOR, [__DIR__, 'data', '2024-12-13_invcls.csv']);

echo "\n";
echo $filepath;

$csv = HCsv::Open($filepath);


while ($row=$csv->readline()) {
	print_r($row);
}
$csv->Close();



echo "\n\n";
