<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;


use Transfashion\KalistaApi\HCsv;

final class Invsite extends Api {
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
		CREATE TABLE tmp_invsite (	
			invsite_id varchar(30),
			dt date,	
			region_id	varchar(5) not null	,
			branch_id	varchar(7) not null	,
			site_id	varchar(90)	,
			heinv_id	varchar(13) not null	,
			saldo_end	int not null default 0	,
			saldo_tts	int not null default 0	,
			C01	int not null default 0	,
			C02	int not null default 0	,
			C03	int not null default 0	,
			C04	int not null default 0	,
			C05	int not null default 0	,
			C06	int not null default 0	,
			C07	int not null default 0	,
			C08	int not null default 0	,
			C09	int not null default 0	,
			C10	int not null default 0	,
			C11	int not null default 0	,
			C12	int not null default 0	,
			C13	int not null default 0	,
			C14	int not null default 0	,
			C15	int not null default 0	,
			C16	int not null default 0	,
			C17	int not null default 0	,
			C18	int not null default 0	,
			C19	int not null default 0	,
			C20	int not null default 0	,
			C21	int not null default 0	,
			C22	int not null default 0	,
			C23	int not null default 0	,
			C24	int not null default 0	,
			C25	int not null default 0	,
			md5checksum	varchar(64)	,
			PRIMARY KEY (region_id, branch_id, heinv_id)		
		)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/
	private function createInvsitebject(array $row) : object {
		$obj = new \stdClass;
		$obj->invsite_id = $row['invsite_id']; 
		$obj->dt = $row['dt']; 
		$obj->region_id = $row['region_id']; 
		$obj->branch_id = $row['branch_id']; 
		$obj->site_id = $row['site_id']; 
		$obj->heinv_id = $row['heinv_id']; 
		$obj->saldo_end = $row['saldo_end']; 
		$obj->saldo_tts = $row['saldo_tts']; 
		$obj->C01 = $row['C01']; 
		$obj->C02 = $row['C02']; 
		$obj->C03 = $row['C03']; 
		$obj->C04 = $row['C04']; 
		$obj->C05 = $row['C05']; 
		$obj->C06 = $row['C06']; 
		$obj->C07 = $row['C07']; 
		$obj->C08 = $row['C08']; 
		$obj->C09 = $row['C09']; 
		$obj->C10 = $row['C10']; 
		$obj->C11 = $row['C11']; 
		$obj->C12 = $row['C12']; 
		$obj->C13 = $row['C13']; 
		$obj->C14 = $row['C14']; 
		$obj->C15 = $row['C15']; 
		$obj->C16 = $row['C16']; 
		$obj->C17 = $row['C17']; 
		$obj->C18 = $row['C18']; 
		$obj->C19 = $row['C19']; 
		$obj->C20 = $row['C20']; 
		$obj->C21 = $row['C21']; 
		$obj->C22 = $row['C22']; 
		$obj->C23 = $row['C23']; 
		$obj->C24 = $row['C24']; 
		$obj->C25 = $row['C25']; 
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
				return $self->createInvsitebject($row);
			};			

			$tablename = "tmp_invsite";
			$primarykey = "invsite_id";

			// hapus dulu datanya
			$db->query('DELETE FROM tmp_invsite');

			// masukkan kembali update datanya
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