<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;

final class Site extends Api {
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
	create tmp_site (
		site_id varchar(30) not null,
		site_name varchar(90),
		site_isclose tinyint(1) not null default 0,
		kalista_site_id varchar(30),
		site_sqm decimal(12,2),
		location_id varchar(30),
		location_name varchar(90),
		city_id varchar(30),
		area_id varchar(30),
		site_code varchar(30),
		sitemodel_id varchar(10),
		site_isdisabled  tinyint(1) not null default 0,
		region_id varchar(5),
		branch_id varchar(7),
		md5checksum varchar(64),
		PRIMARY KEY(site_id),
	) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

	*/
	private function createSiteObject(array $row) : object {
		$obj = new \stdClass;
		$obj->site_id = $row['site_id'];
		$obj->site_name = $row['site_name'];
		$obj->site_isclose = $row['site_isclose'];
		$obj->kalista_site_id = $row['kalista_site_id'];
		$obj->site_sqm = $row['site_sqm'];
		$obj->location_id = $row['location_id'];
		$obj->location_name = $row['location_name'];
		$obj->city_id = $row['city_id'];
		$obj->area_id = $row['area_id'];
		$obj->site_code = $row['site_code'];
		$obj->sitemodel_id = $row['sitemodel_id'];
		$obj->site_isdisabled = $row['site_isdisabled'];
		$obj->region_id = $row['region_id'];
		$obj->branch_id = $row['branch_id'];
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
				return $self->createSiteObject($row);
			};			

			$tablename = "tmp_site";
			$primarykey = "site_id";
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