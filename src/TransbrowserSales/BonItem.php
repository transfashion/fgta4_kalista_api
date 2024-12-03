<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Log;


final class BonItem  {
	public const TBL_BONITEM = "tmp_bonitem";
	private static SqlInsert $cmd_insert;

	public static final function Save(\PDO $db, string $bon_id, array $itemrows) : bool {
		try {
			foreach ($itemrows as $row) {
				$obj = self::createObjectItem($row);
				if (!isset(self::$cmd_insert)) {
					self::$cmd_insert = new SqlInsert(self::TBL_BONITEM, $obj);
					self::$cmd_insert->bind($db);
				}
				self::$cmd_insert->execute($obj);
			}
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	private static function createObjectItem(array $row) : object {
		$obj = new \stdClass;
		$obj->bon_id = $row['bon_id'];
		$obj->bondetil_line = $row['bondetil_line'];
		$obj->bondetil_gro = $row['bondetil_gro'];
		$obj->bondetil_ctg = $row['bondetil_ctg'];
		$obj->bondetil_art = $row['bondetil_art'];
		$obj->bondetil_mat = $row['bondetil_mat'];
		$obj->bondetil_col = $row['bondetil_col'];
		$obj->bondetil_size = $row['bondetil_size'];
		$obj->bondetil_descr = $row['bondetil_descr'];
		$obj->bondetil_qty = $row['bondetil_qty'];
		$obj->bondetil_mpriceori = $row['bondetil_mpriceori'];
		$obj->bondetil_mpricegross = $row['bondetil_mpricegross'];
		$obj->bondetil_mdiscpstd01 = $row['bondetil_mdiscpstd01'];
		$obj->bondetil_mdiscrstd01 = $row['bondetil_mdiscrstd01'];
		$obj->bondetil_mpricenettstd01 = $row['bondetil_mpricenettstd01'];
		$obj->bondetil_mdiscpvou01 = $row['bondetil_mdiscpvou01'];
		$obj->bondetil_mdiscrvou01 = $row['bondetil_mdiscrvou01'];
		$obj->bondetil_mpricecettvou01 = $row['bondetil_mpricecettvou01'];
		$obj->bondetil_vou01id = $row['bondetil_vou01id'];
		$obj->bondetil_vou01codenum = $row['bondetil_vou01codenum'];
		$obj->bondetil_vou01type = $row['bondetil_vou01type'];
		$obj->bondetil_vou01method = $row['bondetil_vou01method'];
		$obj->bondetil_vou01discp = $row['bondetil_vou01discp'];
		$obj->bondetil_mpricenett = $row['bondetil_mpricenett'];
		$obj->bondetil_msubtotal = $row['bondetil_msubtotal'];
		$obj->bondetil_rule = $row['bondetil_rule'];
		$obj->heinv_id = $row['heinv_id'];
		$obj->heinvitem_id = $row['heinvitem_id'];
		$obj->heinvitem_barcode = $row['heinvitem_barcode'];
		$obj->region_id = $row['region_id'];
		$obj->region_nameshort = $row['region_nameshort'];
		$obj->colname = $row['colname'];
		$obj->sizetag = $row['sizetag'];
		$obj->proc = $row['proc'];
		$obj->bon_idext = $row['bon_idext'];
		$obj->pricing_id = $row['pricing_id'];
		$obj->rowid = $row['rowid'];
		$obj->season_id = $row['season_id'];

		return $obj;
	}	

}