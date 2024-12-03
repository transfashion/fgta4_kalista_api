<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Log;


final class Bon  {
	public const TBL_BON = "tmp_bon";
	private static SqlInsert $cmd_insert;

	public static final function Save(\PDO $db, string $bon_id, array $row) : bool {
		try {
			$obj = self::createObjectHeader($row);
			if (!isset(self::$cmd_insert)) {
				self::$cmd_insert = new SqlInsert(self::TBL_BON, $obj);
				self::$cmd_insert->bind($db);
			}
			self::$cmd_insert->execute($obj);
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}


	public static function createObjectHeader(array $row) : object {
		$obj = new \stdClass;
		$obj->bon_id = $row['bon_id'];
		$obj->bon_idext = $row['bon_idext'];
		$obj->bon_event = $row['bon_event'];
		$obj->bon_date = $row['bon_date'];
		$obj->bon_createby = $row['bon_createby'];
		$obj->bon_createdate = $row['bon_createdate'];
		$obj->bon_modifyby = $row['bon_modifyby'];
		$obj->bon_modifydate = $row['bon_modifydate'] ? $row['bon_modifydate'] : null;
		$obj->bon_isvoid = $row['bon_isvoid'];
		$obj->bon_voidby = $row['bon_voidby'];
		$obj->bon_voiddate = $row['bon_voiddate'] ? $row['bon_voiddate'] : null;
		$obj->bon_replacefromvoid = $row['bon_replacefromvoid'];
		$obj->bon_msubtotal = $row['bon_msubtotal'];
		$obj->bon_msubtvoucher = $row['bon_msubtvoucher'];
		$obj->bon_msubtdiscadd = $row['bon_msubtdiscadd'];
		$obj->bon_msubtredeem = $row['bon_msubtredeem'];
		$obj->bon_msubtracttotal = $row['bon_msubtracttotal'];
		$obj->bon_msubtotaltobedisc = $row['bon_msubtotaltobedisc'];
		$obj->bon_mdiscpaympercent = $row['bon_mdiscpaympercent'];
		$obj->bon_mdiscpayment = $row['bon_mdiscpayment'];
		$obj->bon_mtotal = $row['bon_mtotal'];
		$obj->bon_mpayment = $row['bon_mpayment'];
		$obj->bon_mrefund = $row['bon_mrefund'];
		$obj->bon_msalegross = $row['bon_msalegross'];
		$obj->bon_msaletax = $row['bon_msaletax'];
		$obj->bon_msalenet = $row['bon_msalenet'];
		$obj->bon_itemqty = $row['bon_itemqty'];
		$obj->bon_rowitem = $row['bon_rowitem'];
		$obj->bon_rowpayment = $row['bon_rowpayment'];
		$obj->bon_npwp = $row['bon_npwp'];
		$obj->bon_fakturpajak = $row['bon_fakturpajak'];
		$obj->bon_adddisc_authusername = $row['bon_adddisc_authusername'];
		$obj->bon_disctype = $row['bon_disctype'];
		$obj->customer_id = $row['customer_id'];
		$obj->customer_name = $row['customer_name'];
		$obj->customer_telp = $row['customer_telp'];
		$obj->customer_npwp = $row['customer_npwp'];
		$obj->customer_ageid = $row['customer_ageid'];
		$obj->customer_agename = $row['customer_agename'];
		$obj->customer_genderid = $row['customer_genderid'];
		$obj->customer_gendername = $row['customer_gendername'];
		$obj->customer_nationalityid = $row['customer_nationalityid'];
		$obj->customer_nationalityname = $row['customer_nationalityname'];
		$obj->customer_typename = $row['customer_typename'];
		$obj->customer_passport = $row['customer_passport'];
		$obj->customer_disc = $row['customer_disc'];
		$obj->voucher01_id = $row['voucher01_id'];
		$obj->voucher01_name = $row['voucher01_name'];
		$obj->voucher01_codenum = $row['voucher01_codenum'];
		$obj->voucher01_method = $row['voucher01_method'];
		$obj->voucher01_type = $row['voucher01_type'];
		$obj->voucher01_discp = $row['voucher01_discp'];
		$obj->salesperson_id = $row['salesperson_id'];
		$obj->salesperson_name = $row['salesperson_name'];
		$obj->pospayment_id = $row['pospayment_id'];
		$obj->pospayment_name = $row['pospayment_name'];
		$obj->posedc_id = $row['posedc_id'];
		$obj->posedc_name = $row['posedc_name'];
		$obj->machine_id = $row['machine_id'];
		$obj->region_id = $row['region_id'];
		$obj->branch_id = $row['branch_id'];
		$obj->syncode = $row['syncode'];
		$obj->syndate = $row['syndate'];
		$obj->rowid = $row['rowid'];
		$obj->site_id_from = $row['site_id_from'];

		return $obj;
	}


}