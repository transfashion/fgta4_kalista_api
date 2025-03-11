<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Heinv extends Api {
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
		CREATE TABLE tmp_heinv (		
			heinv_id	varchar(13) not null	,
			heinv_art	varchar(30)	,
			heinv_mat	varchar(30)	,
			heinv_col	varchar(30)	,
			heinv_name	varchar(255)	,
			heinvctg_id	varchar(10)	,
			invcls_id	varchar(10)	,
			pcp_line	varchar(50)	,
			pcp_gro	varchar(50)	,
			pcp_ctg	varchar(50)	,
			heinv_coldescr	varchar(30)	,
			gtype	varchar(10)	,
			gender	varchar(10)	,
			fit	varchar(50)	,
			region_id	varchar(50)	,
			season_group	varchar(20)	,
			season_id	varchar(10)	,
			heinv_priceori	decimal(15,0)	,
			heinv_priceadj	decimal(15,0)	,
			heinv_pricegross	decimal(15,0)	,
			heinv_price	decimal(15,0)	,
			heinv_pricedisc	decimal(15,0)	,
			heinv_pricenett	decimal(15,0)	,
			discflag	varchar(10)	,
			deftype_id	varchar(10)	,
			RVID	varchar(30)	,
			RVDT	date	,
			age	int	,
			lastcost	decimal(17,2)	,
			md5checksum	varchar(64)	,
			PRIMARY KEY (heinv_id)		
		)  ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;		
	*/
	private function createHeinvObject(array $row) : object {
		$obj = new \stdClass;
		$obj->heinv_id = $row['heinv_id']; 
		$obj->heinv_art = $row['heinv_art']; 
		$obj->heinv_mat = $row['heinv_mat']; 
		$obj->heinv_col = $row['heinv_col']; 
		$obj->heinv_name = $row['heinv_name']; 
		$obj->heinvctg_id = $row['heinvctg_id']; 
		$obj->invcls_id = $row['invcls_id']; 
		$obj->pcp_line = $row['pcp_line']; 
		$obj->pcp_gro = $row['pcp_gro']; 
		$obj->pcp_ctg = $row['pcp_ctg']; 
		$obj->heinv_coldescr = $row['heinv_coldescr']; 
		$obj->gtype = $row['gtype']; 
		$obj->gender = $row['gender']; 
		$obj->fit = $row['fit']; 
		$obj->region_id = $row['region_id']; 
		$obj->season_group = $row['season_group']; 
		$obj->season_id = $row['season_id']; 
		$obj->heinv_priceori = $row['heinv_priceori']; 
		$obj->heinv_priceadj = $row['heinv_priceadj']; 
		$obj->heinv_pricegross = $row['heinv_pricegross']; 
		$obj->heinv_price = $row['heinv_price']; 
		$obj->heinv_pricedisc = $row['heinv_pricedisc']; 
		$obj->heinv_pricenett = $row['heinv_pricenett']; 
		$obj->discflag = $row['discflag']; 
		$obj->deftype_id = $row['deftype_id']; 
		$obj->RVID = $row['RVID']; 
		$obj->RVDT = $row['RVDT']; 
		$obj->age = $row['age']; 
		$obj->order_id = $row['order_id'] == '' ? null :  $row['order_id'];
		$obj->heinv_fob = $row['heinv_fob'] == '' ? null : $row['heinv_fob'];
		$obj->curr_id = $row['curr_id'] == '' ? null : $row['curr_id'];
		$obj->lastcost = $row['lastcost']; 
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
				return $self->createHeinvObject($row);
			};			

			$tablename = "tmp_heinv";
			$primarykey = "heinv_id";
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