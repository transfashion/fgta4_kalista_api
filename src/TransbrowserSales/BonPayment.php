<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Log;


final class BonPayment  {
	public const TBL_BONPAYMENT = "tmp_bonpayment";
	private static SqlInsert $cmd_insert;


	public static final function Save(\PDO $db, string $bon_id, array $paymentrows) : bool {
		try {
			foreach ($paymentrows as $row) {
				$obj = self::createObjectPayment($row);
				if (!isset(self::$cmd_insert)) {
					self::$cmd_insert = new SqlInsert(self::TBL_BONPAYMENT, $obj);
					self::$cmd_insert->bind($db);
				}
				self::$cmd_insert->execute($obj);
			}
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

	private static function createObjectPayment(array $row) : object {
		$obj = new \stdClass;
		$obj->bon_id = $row['bon_id'];
		$obj->payment_line = $row['payment_line'];
		$obj->payment_cardnumber = $row['payment_cardnumber'];
		$obj->payment_cardholder = $row['payment_cardholder'];
		$obj->payment_mvalue = $row['payment_mvalue'];
		$obj->payment_mcash = $row['payment_mcash'];
		$obj->payment_installment = $row['payment_installment'];
		$obj->pospayment_id = $row['pospayment_id'];
		$obj->pospayment_name = $row['pospayment_name'];
		$obj->pospayment_bank = $row['pospayment_bank'];
		$obj->posedc_id = $row['posedc_id'];
		$obj->posedc_name = $row['posedc_name'];
		$obj->posedc_approval = $row['posedc_approval'];
		$obj->bon_idext = $row['bon_idext'];
		$obj->rowid = $row['rowid'];

		return $obj;
	}

}