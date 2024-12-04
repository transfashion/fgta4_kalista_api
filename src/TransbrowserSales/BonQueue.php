<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;
use AgungDhewe\PhpSqlUtil\SqlUpdate;

use Transfashion\KalistaApi\Log;


final class BonQueue  {
	public const TBL_QUEUE = "queue_bon";
	private static SqlInsert $cmd_insert;
	private static SqlUpdate $cmd_update;
	private static SqlSelect $cmd_cek;


	public static final function Save(\PDO $db, string $bon_id) : bool {
		try {
			// siapkan data queue
			$obj = new \stdClass;
			$obj->bon_id = $bon_id;
			$obj->bon_timestamp = date('Y-m-d H:i:s');


			// cek dulu apakah sudah ada di queue
			$objcek = new \stdClass;
			$objcek->bon_id = $bon_id;
			if (!isset(self::$cmd_cek)) {
				self::$cmd_cek = new SqlSelect(self::TBL_QUEUE, $objcek);
				self::$cmd_cek->bind($db);
			}
			self::$cmd_cek->execute($objcek);
			$row = self::$cmd_cek->fetch();
			if (empty($row)) {
				// insert queue baru
				if (!isset(self::$cmd_insert)) {
					self::$cmd_insert = new SqlInsert(self::TBL_QUEUE, $obj);
					self::$cmd_insert->bind($db);
				}
				self::$cmd_insert->execute($obj);
			} else {
				// update timestamp queue
				if (!isset(self::$cmd_update)) {
					self::$cmd_update = new SqlUpdate(self::TBL_QUEUE, $obj, ['bon_id']);
					self::$cmd_update->bind($db);
				}
				self::$cmd_update->execute($obj);
			}

			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
}