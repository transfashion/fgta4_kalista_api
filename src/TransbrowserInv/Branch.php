<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Branch extends Api {
	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	/*
	CREATE TABLE tmp_branch (		
		branch_id	varchar(7) not null	,
		branch_name	varchar(30)	,
		md5checksum	varchar(64)	,
		PRIMARY KEY (branch_id)		
	)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/

	private function ConnectDatabase() : \PDO {
		$cfgkey = Configuration::GetUsedConfig(Configuration::DB_RPT);
		$dbconfig = Configuration::Get($cfgkey);
		Database::Connect(Configuration::DB_RPT, $dbconfig);
		$db = Database::GetConnection(Configuration::DB_RPT);
		return $db;
	}

	private function createBranchbject(array $row) : object {
		$obj = new \stdClass;
		$obj->branch_id = $row['branch_id']; 
		$obj->branch_name = $row['branch_name']; 
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
				return $self->createBranchbject($row);
			};			

			$tablename = "tmp_branch";
			$primarykey = "branch_id";
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