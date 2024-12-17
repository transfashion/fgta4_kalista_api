<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Sizetag extends Api {
	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	private function ConnectDatabase() : \PDO {
		$cfgkey = Configuration::GetUsedConfig(Configuration::DB_RPT);
		$dbconfig = Configuration::Get($cfgkey);
		Database::Connect(Configuration::DB_RPT, $dbconfig);
		$db = Database::GetConnection(Configuration::DB_RPT);
		return $db;
	}	

	/*
		CREATE TABLE tmp_sizetag (		
			sizetag_id	varchar(30),
			region_id	varchar(5)	not null,
			sizetag	varchar(5)	not null,
			colnum	varchar(2)	not null,
			size	varchar(10)	,
			md5checksum	varchar(64)	,
			PRIMARY KEY (region_id, sizetag, colnum)		
		)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/
	private function createSizetagObject(array $row) : object {
		$obj = new \stdClass;
		$obj->sizetag_id = $row['sizetag_id']; 
		$obj->region_id = $row['region_id']; 
		$obj->sizetag = $row['sizetag']; 
		$obj->colnum = $row['colnum']; 
		$obj->size = $row['size']; 
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
				return $self->createSizetagObject($row);
			};			

			$tablename = "tmp_sizetag";
			$primarykey = "sizetag_id";
			$csv = HCsv::Open($file);
			$csv->syncToTable($db, $tablename, $primarykey, $createTableObject);
			$csv->Close();

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