<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use Transfashion\KalistaApi\HCsv;


final class InvCls extends Api {



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


	private function createInvclsObject(array $row) : object {
		$obj = new \stdClass;
		$obj->invcls_id = $row['invcls_id'];
		$obj->invcls_name = $row['invcls_name'];
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


			$tablename = "tmp_invcls";
			$csv = HCsv::Open($file);
			while ($row=$csv->readline()) {
				$obj = $this->createInvclsObject($row);

				if (!isset($cmd_cek)) {
					$cek = new \stdClass;
					$cek->invcls_id = $row['invcls_id'];
					$cmd = new SqlSelect($tablename , $cek);
					$cmd->bind($db);
					$cmd_cek = $cmd;
				}
				

				
				if (!isset($cmd_insert)) {
					

				}


			}
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