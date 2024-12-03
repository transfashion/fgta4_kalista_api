<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Log;

final class BonReport  {
	public const TBL_REPORT = "rpt_sales";
	private static SqlInsert $cmd_insert;

	public static final function Save(\PDO $db, string $bon_id, array $reportrows) : bool {
		try {
			foreach ($reportrows as $row) {
				$obj = self::createObjectReport($row);
				if (!isset(self::$cmd_insert)) {
					self::$cmd_insert = new SqlInsert(self::TBL_REPORT, $obj);
					self::$cmd_insert->bind($db);
				}
				self::$cmd_insert->execute($obj);
			}
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

	private static function createObjectReport(array $row) : object {
		$obj = new \stdClass;
		$obj->bon_id = $row['bon_id'];
		$obj->bondetil_line = $row['bondetil_line'];
		$obj->bon_isvoid = $row['bon_isvoid'];
		$obj->bon_date = $row['bon_date'];
		$obj->bon_time = $row['bon_time'];
		$obj->bon_year = $row['bon_year'];
		$obj->bon_month = $row['bon_month'];
		$obj->bon_ym = $row['bon_ym'];
		$obj->bon_datestr = $row['bon_datestr'];
		$obj->bon_hour = $row['bon_hour'];
		$obj->bon_week = $row['bon_week'];
		$obj->bon_day = $row['bon_day'];
		$obj->bon_event = $row['bon_event'];
		$obj->payment_id = $row['payment_id'];
		$obj->payment_name = $row['payment_name'];
		$obj->payment_bank = $row['payment_bank'];
		$obj->payment_cardnumber = $row['payment_cardnumber'];
		$obj->payment_cardholder = $row['payment_cardholder'];
		$obj->payment_value = $row['payment_value'];
		$obj->otherpayment_value = $row['otherpayment_value'];
		$obj->machine_id = $row['machine_id'];
		$obj->bon_region_id = $row['bon_region_id'];
		$obj->region_id = $row['region_id'];
		$obj->branch_id = $row['branch_id'];
		$obj->brand_name = $row['brand_name'];
		$obj->site_id = $row['site_id'];
		$obj->site_name = $row['site_name'];
		$obj->city_id = $row['city_id'];
		$obj->area_id = $row['area_id'];
		$obj->sitemodel_id = $row['sitemodel_id'];
		$obj->location_id = $row['location_id'];
		$obj->location_name = $row['location_name'];
		$obj->salesperson_id = $row['salesperson_id'];
		$obj->salesperson_nik = $row['salesperson_nik'];
		$obj->salesperson_name = $row['salesperson_name'];
		$obj->customer_id = $row['customer_id'];
		$obj->customer_telp = $row['customer_telp'];
		$obj->customer_name = $row['customer_name'];
		$obj->customer_email = $row['customer_email'];
		$obj->heinv_id = $row['heinv_id'];
		$obj->heinvitem_id = $row['heinvitem_id'];
		$obj->heinvitem_barcode = $row['heinvitem_barcode'];
		$obj->heinv_art = $row['heinv_art'];
		$obj->heinv_mat = $row['heinv_mat'];
		$obj->heinv_col = $row['heinv_col'];
		$obj->bondetil_size = $row['bondetil_size'];
		$obj->heinv_name = $row['heinv_name'];
		$obj->heinv_iskonsinyasi = $row['heinv_iskonsinyasi'];
		$obj->heinvgro_id = $row['heinvgro_id'];
		$obj->heinvgro_name = $row['heinvgro_name'];
		$obj->heinvctg_id = $row['heinvctg_id'];
		$obj->heinvctg_name = $row['heinvctg_name'];
		$obj->heinvctg_costadd = $row['heinvctg_costadd'];
		$obj->heinvctg_mf = $row['heinvctg_mf'];
		$obj->invcls_id = $row['invcls_id'];
		$obj->invcls_name = $row['invcls_name'];
		$obj->invcls_descr = $row['invcls_descr'];
		$obj->invcls_gro = $row['invcls_gro'];
		$obj->heinv_fit = $row['heinv_fit'];
		$obj->heinv_colordescr = $row['heinv_colordescr'];
		$obj->heinv_gender = $row['heinv_gender'];
		$obj->pcp_line = $row['pcp_line'];
		$obj->pcp_gro = $row['pcp_gro'];
		$obj->pcp_ctg = $row['pcp_ctg'];
		$obj->mdflag = $row['mdflag'];
		$obj->heinv_gtype = $row['heinv_gtype'];
		$obj->season_group = $row['season_group'];
		$obj->season_id = $row['season_id'];
		$obj->heorder_id = $row['heorder_id'];
		$obj->heinv_lastrvid = $row['heinv_lastrvid'];
		$obj->heinv_lastrvdate = $row['heinv_lastrvdate'];
		$obj->rvage = $row['rvage'];
		$obj->heinv_lasttrid = $row['heinv_lasttrid'];
		$obj->heinv_lasttrdate = $row['heinv_lasttrdate'];
		$obj->trage = $row['trage'];
		$obj->promo_id = $row['promo_id'];
		$obj->promo_codenum = $row['promo_codenum'];
		$obj->promo_type = $row['promo_type'];
		$obj->promo_line = $row['promo_line'];
		$obj->promo_method = $row['promo_method'];
		$obj->promo_discp = $row['promo_discp'];
		$obj->sales_qty = $row['sales_qty'];
		$obj->sales_itemgross = $row['sales_itemgross'];
		$obj->sales_itemnett = $row['sales_itemnett'];
		$obj->sales_nett = $row['sales_nett'];
		$obj->currency_id = $row['currency_id'];
		$obj->heinv_fob = $row['heinv_fob'];
		$obj->heinv_lastcost = $row['heinv_lastcost'];
		$obj->cogs_estimated = $row['cogs_estimated'];
		$obj->cogs_final = $row['cogs_final'];

		return $obj;
	}
}