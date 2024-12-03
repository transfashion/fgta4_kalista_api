<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransBrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;



final class SyncSales extends Api {


	private \PDOStatement $stmt_del_report;
	private \PDOStatement $stmt_del_bon;
	private \PDOStatement $stmt_del_bonitem;
	private \PDOStatement $stmt_del_bonpayment;
	


	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}


	public static final function decodeData(string $data) : array {
		$compressed_jsondata = base64_decode($data);
		$jsondata = gzuncompress($compressed_jsondata);
		$data = json_decode($jsondata, true);
		return $data;
	}

	/**
	 * @ApiMethod
	 * 
	 */
	public final function Sync(string $data) : array {
		
		$success = false;
		$message = "";
		
		try {
			$cfgkey = Configuration::GetUsedConfig(Configuration::DB_RPT);
			$dbconfig = Configuration::Get($cfgkey);
			Database::Connect(Configuration::DB_RPT, $dbconfig);
			Log::info('Databse Connected!');

			$db = Database::GetConnection(Configuration::DB_RPT);

			$data = self::decodeData($data);
			$header = $data["header"];
			$items = $data["items"];
			$payments = $data["payments"];
			$report = $data["report"];
			$bon_id = $header['bon_id'];


			$db->beginTransaction();
			try {
				$this->delete($bon_id);
				BonReport::Save($db, $bon_id, $report);
				Bon::Save($db, $bon_id, $header);
				BonItem::Save($db, $bon_id, $items);
				BonPayment::Save($db, $bon_id, $payments);

				$db->commit();
				$success = true;
			} catch (\Exception $ex) {
				$db->rollBack();
				throw $ex;
			} 
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


	private function delete(string $bon_id) : bool {
		$db = Database::GetConnection(Configuration::DB_RPT);

		try {
			if (!isset($this->stmt_del_report)) {
				$query = "delete from rpt_sales where bon_id = :bon_id";
				$this->stmt_del_report = $db->prepare($query);
			}

			if (!isset($this->stmt_del_bon)) {
				$query = "delete from tmp_bon where bon_id = :bon_id";
				$this->stmt_del_bon = $db->prepare($query);
			}

			if (!isset($this->stmt_del_bonitem)) {
				$query = "delete from tmp_bonitem where bon_id = :bon_id";
				$this->stmt_del_bonitem = $db->prepare($query);
			}

			if (!isset($this->stmt_del_bonpayment)) {
				$query = "delete from tmp_bonpayment where bon_id = :bon_id";
				$this->stmt_del_bonpayment = $db->prepare($query);
			}

			$this->stmt_del_report->execute([":bon_id" => $bon_id]);
			$this->stmt_del_bon->execute([":bon_id" => $bon_id]);
			$this->stmt_del_bonitem->execute([":bon_id" => $bon_id]);
			$this->stmt_del_bonpayment->execute([":bon_id" => $bon_id]);

			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
}