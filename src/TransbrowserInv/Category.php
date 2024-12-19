<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Category extends Api {
	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	/*
	CREATE TABLE tmp_heinvctg (		
		heinvctg_id	varchar(10) not null	,
		heinvctg_name	varchar(50)	,
		heinvctg_sizetag	varchar(5)	,
		heinvgro_id	varchar(10)	,
		heinvgro_name	varchar(30)	,
		region_id	varchar(5)	,
		md5checksum	varchar(64)	,
		PRIMARY KEY (heinvctg_id)		
	)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/


	private function ConnectDatabase() : \PDO {
		$cfgkey = Configuration::GetUsedConfig(Configuration::DB_RPT);
		$dbconfig = Configuration::Get($cfgkey);
		Database::Connect(Configuration::DB_RPT, $dbconfig);
		$db = Database::GetConnection(Configuration::DB_RPT);
		return $db;
	}

	private function createCategorybject(array $row) : object {
		$obj = new \stdClass;
		$obj->heinvctg_id = $row['heinvctg_id']; 
		$obj->heinvctg_name = $row['heinvctg_name']; 
		$obj->heinvctg_sizetag = $row['heinvctg_sizetag']; 
		$obj->heinvgro_id = $row['heinvgro_id']; 
		$obj->heinvgro_name = $row['heinvgro_name']; 
		$obj->region_id = $row['region_id']; 
		$obj->md5checksum = $row['md5checksum']; 
		return $obj;
	}


	/**
	 * @ApiMethod
	 */
	public function Process(string $filename) : array {
		$success = false;
		$message = "";

		try {
			$db = $this->ConnectDatabase();
			$rootDir = Configuration::GetRootDir();
			$file = join(DIRECTORY_SEPARATOR, [$rootDir, 'data', $filename]);

			$self = $this;
			$createTableObject = function($row) use ($self) {
				return $self->createCategorybject($row);
			};			

			$tablename = "tmp_heinvctg";
			$primarykey = "heinvctg_id";
			$csv = HCsv::Open($file);
			$csv->syncToTable($db, $tablename, $primarykey, $createTableObject);
			$csv->Close();

			unlink($file);

			$success = true;
		} catch (\Exception $ex) {
			$success = false;
			$message = $ex->getMessage();
		} finally {
			return [
				"success" => $success,
				"message" => $message
			];
		}

	}
}