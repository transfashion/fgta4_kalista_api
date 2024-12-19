<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Region extends Api {
	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	/*
	CREATE TABLE tmp_region (		
		region_id	varchar(5) not null	,
		region_name	varchar(30)	,
		md5checksum	varchar(64)	,
		PRIMARY KEY (region_id)		
	)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/


	private function ConnectDatabase() : \PDO {
		$cfgkey = Configuration::GetUsedConfig(Configuration::DB_RPT);
		$dbconfig = Configuration::Get($cfgkey);
		Database::Connect(Configuration::DB_RPT, $dbconfig);
		$db = Database::GetConnection(Configuration::DB_RPT);
		return $db;
	}

	private function createRegionbject(array $row) : object {
		$obj = new \stdClass;
		$obj->region_id = $row['region_id']; 
		$obj->region_name = $row['region_name']; 
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
				return $self->createRegionbject($row);
			};			

			$tablename = "tmp_region";
			$primarykey = "region_id";
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