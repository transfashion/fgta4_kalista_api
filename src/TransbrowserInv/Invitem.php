<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use \PDO;
use \PDOStatement;

final class Invitem extends Api {

	private PDOStatement $stmt_category_get;
	private PDOStatement $stmt_heinv_get;
	private PDOStatement $stmt_invcls_get;
	private PDOStatement $stmt_site_get;
	private PDOStatement $stmt_sizetag_get;
	private PDOStatement $stmt_branch_get;
	private PDOStatement $stmt_region_get;

	private array $Category = [];
	private array $Heinv = [];
	private array $Invcls = [];
	private array $Site = [];
	private array $Sizetag = [];
	private array $Branch = [];
	private array $Region = [];


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

	/**
	 * @ApiMethod
	 */
	public final function CreateItemSummary(string $date, string $region_id) : array {
		$success = false;
		$message = "";
		$tablename = 'tmp_invitemposition';

		$test_max_row = 0;
		$cr = 0;

		try {
			$db = $this->ConnectDatabase();
			$db->query("delete from $tablename");

			$this->getSizetag($db, $region_id);

			$sql = "
				select 
				A.dt,
				A.region_id,
				A.branch_id,
				A.site_id,
				A.heinv_id,
				A.C01, A.C02, A.C03, A.C04, A.C05,
				A.C06, A.C07, A.C08, A.C09, A.C10,
				A.C11, A.C12, A.C13, A.C14, A.C15,
				A.C16, A.C17, A.C18, A.C19, A.C20,
				A.C21, A.C22, A.C23, A.C24, A.C25
				from tmp_invsite A where A.dt=:date and A.region_id=:region_id
			";
			$stmt = $db->prepare($sql);
			$stmt->execute([':date'=>$date, ':region_id'=>$region_id]);
			$rows = $stmt->fetchAll();
			foreach ($rows as $row) {
				$cr++;
				if ($test_max_row>0) {
					if ($cr>=$test_max_row) {
						break;
					}
				}


				$heinv_id = $row['heinv_id'];
				$site_id = $row['site_id'];
				$branch_id = $row['branch_id'];

				$this->getRegionData($db, $region_id);
				$this->getBranchData($db, $branch_id);
				$this->getSiteData($db, $site_id);
				$this->getHeinvData($db, $heinv_id);

				$invcls_id = $this->Heinv[$heinv_id]['invcls_id'];
				$this->getInvclsData($db, $invcls_id);

				$heinvctg_id = $this->Heinv[$heinv_id]['heinvctg_id'];
				$this->getCategoryData($db, $heinvctg_id);


				$row['invcls_id'] = $invcls_id;
				$row['heinvctg_id'] = $heinvctg_id;

				$obj = $this->createHeinvItemObject($row); 
				for ($i=1; $i<=25; $i++) {
					$colnum = str_pad((string)$i, 2, "0", STR_PAD_LEFT);
					$colname = "C" . $colnum;
					$qty = (int)$row[$colname];
					if ($qty==0) {
						continue;
					}

					$sizetag_id = "{$region_id}-{$obj->heinvctg_sizetag}-{$colnum}";
					if (array_key_exists($sizetag_id, $this->Sizetag)) {
						$obj->heinv_size = $this->Sizetag[$sizetag_id]['size'];
					} else {
						$obj->heinv_size = '----------';
					}


					$obj->heinv_colnum = $colnum;
					$obj->heinvitem_id = substr($obj->heinv_id, 0, 11) . $colnum;
					$obj->total_qty = $qty;
					$obj->total_value = $obj->lastcost * $qty;

					if (!isset($cmd_insert)) {
						$cmd = new SqlInsert($tablename, $obj);
						$cmd->bind($db);
						$cmd_insert = $cmd;
					}
					$cmd = $cmd_insert;
					$cmd->execute($obj);
				}

				Log::info("$site_id $heinv_id");
			}

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

	private function getCategoryData(PDO $db, string $heinvctg_id) : void {
		if (array_key_exists($heinvctg_id, $this->Category)) {
			return;
		}

		try {
			if (!isset($this->stmt_category_get)) {
				$sql = "select * from tmp_heinvctg where heinvctg_id = :heinvctg_id";
				$stmt = $db->prepare($sql);
				$this->stmt_category_get = $stmt;
			}

			$stmt = $this->stmt_category_get;
			$stmt->execute([':heinvctg_id'=>$heinvctg_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$heinvctg_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Category[$heinvctg_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private function getHeinvData(PDO $db, string $heinv_id) : void {
		if (array_key_exists($heinv_id, $this->Heinv)) {
			return;
		}

		try {
			if (!isset($this->stmt_heinv_get)) {
				$sql = "select * from tmp_heinv where heinv_id = :heinv_id";
				$stmt = $db->prepare($sql);
				$this->stmt_heinv_get = $stmt;
			}

			$stmt = $this->stmt_heinv_get;
			$stmt->execute([':heinv_id'=>$heinv_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$heinv_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Heinv[$heinv_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private function getInvclsData(PDO $db, string $invcls_id) : void {
		if (array_key_exists($invcls_id, $this->Invcls)) {
			return;
		}

		try {
			if (!isset($this->stmt_invcls_get)) {
				$sql = "select * from tmp_invcls where invcls_id = :invcls_id";
				$stmt = $db->prepare($sql);
				$this->stmt_invcls_get = $stmt;
			}

			$stmt = $this->stmt_invcls_get;
			$stmt->execute([':invcls_id'=>$invcls_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$invcls_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Invcls[$invcls_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private function getSiteData(PDO $db, string $site_id) : void {
		if (array_key_exists($site_id, $this->Site)) {
			return;
		}

		try {
			if (!isset($this->stmt_site_get)) {
				$sql = "select * from tmp_site where site_id = :site_id";
				$stmt = $db->prepare($sql);
				$this->stmt_site_get = $stmt;
			}

			$stmt = $this->stmt_site_get;
			$stmt->execute([':site_id'=>$site_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$site_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Site[$site_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}
	
	private function getBranchData(PDO $db, string $branch_id) : void {
		if (array_key_exists($branch_id, $this->Branch)) {
			return;
		}

		try {
			if (!isset($this->stmt_branch_get)) {
				$sql = "select * from tmp_branch where branch_id = :branch_id";
				$stmt = $db->prepare($sql);
				$this->stmt_branch_get = $stmt;
			}

			$stmt = $this->stmt_branch_get;
			$stmt->execute([':branch_id'=>$branch_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$branch_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Branch[$branch_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	private function getRegionData(PDO $db, string $region_id) : void {
		if (array_key_exists($region_id, $this->Region)) {
			return;
		}

		try {
			if (!isset($this->stmt_region_get)) {
				$sql = "select * from tmp_region where region_id = :region_id";
				$stmt = $db->prepare($sql);
				$this->stmt_region_get = $stmt;
			}

			$stmt = $this->stmt_region_get;
			$stmt->execute([':region_id'=>$region_id]);
			$row = $stmt->fetch();
			if ($row==null) {
				$errmsg = Log::error("$region_id tidak ditemukan");
				throw new \Exception($errmsg);
			}
			$this->Region[$region_id] = $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}



	private function getSizetag(PDO $db, string $region_id) : void {
		try {
			if (!isset($this->stmt_sizetag_get)) {
				$sql = "select * from tmp_sizetag where region_id = :region_id";
				$stmt = $db->prepare($sql);
				$this->stmt_sizetag_get = $stmt;
			}

			$stmt = $this->stmt_sizetag_get;
			$stmt->execute([':region_id'=>$region_id]);
			$rows = $stmt->fetchall();
			foreach ($rows as $row) {
				$sizetag_id = $row['sizetag_id'];
				$this->Sizetag[$sizetag_id] = $row;
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private function createHeinvItemObject(array $row) : object {
		/* keys */
		$heinv_id = $row['heinv_id'];
		$site_id = $row['site_id'];
		$invcls_id = $row['invcls_id'];
		$heinvctg_id = $row['heinvctg_id'];
		$region_id = $row['region_id'];
		$branch_id = $row['branch_id'];

		/* Source Lookup */
		$Heinv = $this->Heinv[$heinv_id];
		$Site = $this->Site[$site_id];
		$Invcls = $this->Invcls[$invcls_id];
		$Category = $this->Category[$heinvctg_id];
		$Branch = $this->Branch[$branch_id];
		$Region = $this->Region[$region_id];


		/* item object */
		$obj = new \stdClass;
		$obj->dt = $row['dt'];
		$obj->region_id = $region_id;
		$obj->region_name = $Region['region_name'];
		$obj->branch_id = $branch_id;
		$obj->branch_name = $Branch['branch_name'];
		

		/* lookup from Heinv */
		$obj->heinv_id = $heinv_id;
		// $obj->heinvitem_id = $Heinv['heinvitem_id']  // diisi di luar
		$obj->heinv_art = $Heinv['heinv_art'];
		$obj->heinv_mat = $Heinv['heinv_mat'];
		$obj->heinv_col = $Heinv['heinv_col'];
		$obj->heinv_coldescr = $Heinv['heinv_coldescr'];
		//$obj->heinv_size = $Heinv['heinv_size']; // diisi di luar
		//$obj->heinv_colnum = $Heinv['heinv_colnum']; // diisi di luar
		$obj->heinv_name = $Heinv['heinv_name'];
		$obj->heinv_priceori = $Heinv['heinv_priceori'];
		$obj->heinv_priceadj = $Heinv['heinv_priceadj'];
		$obj->heinv_pricegross = $Heinv['heinv_pricegross'];
		$obj->heinv_price = $Heinv['heinv_price'];
		$obj->heinv_pricedisc = $Heinv['heinv_pricedisc'];
		$obj->heinv_pricenett = $Heinv['heinv_pricenett'];
		$obj->discflag = $Heinv['discflag'];
		$obj->pcp_line = $Heinv['pcp_line'];
		$obj->pcp_gro = $Heinv['pcp_gro'];
		$obj->pcp_ctg = $Heinv['pcp_ctg'];
		$obj->gtype = $Heinv['gtype'];
		$obj->gender = $Heinv['gender'];
		$obj->fit = $Heinv['fit'];
		$obj->season_group = $Heinv['season_group'];
		$obj->season_id = $Heinv['season_id'];
		$obj->deftype_id = $Heinv['deftype_id'];
		$obj->RVID = $Heinv['RVID'];
		if ($Heinv['RVDT']!=null) {
			$obj->RVDT = $Heinv['RVDT'];
		}
		$obj->age = $Heinv['age'];
		$obj->lastcost = (float)$Heinv['lastcost'];

		/* lookup from Site */
		$obj->site_id = $site_id;
		$obj->site_name = $Site['site_name'];
		$obj->site_sqm = $Site['site_sqm'];
		$obj->site_code = $Site['site_code'];
		$obj->site_isclose = $Site['site_isclose'];
		$obj->site_isdisabled = $Site['site_isdisabled'];
		$obj->location_id = $Site['location_id'];
		$obj->location_name = $Site['location_name'];		
		$obj->city_id = $Site['city_id'];
		$obj->area_id = $Site['area_id'];
		$obj->sitemodel_id = $Site['sitemodel_id'];
		$obj->kalista_site_id = $Site['kalista_site_id'];		

		/* lookup from Invcls */
		$obj->invcls_id = $invcls_id ;
		$obj->invcls_name = $Invcls['invcls_name'];
		
		/* lookup from Category */
		$obj->heinvctg_id = $heinvctg_id;
		$obj->heinvctg_name = $Category['heinvctg_name'];
		$obj->heinvctg_sizetag = $Category['heinvctg_sizetag'];
		$obj->heinvgro_id = $Category['heinvgro_id'];
		$obj->heinvgro_name = $Category['heinvgro_name'];
		$obj->mdflag = str_ends_with($obj->heinvgro_id, '00000') ? 'MD0' : 'MD1';

		return $obj;
	}

	/*
	DROP TABLE IF EXISTS tmp_invitemposition;
	CREATE TABLE tmp_invitemposition (
		dt date,
		region_id varchar(5),
		region_name varchar(30),
		branch_id varchar(7),
		branch_name varchar(30),
		
		-- from Heinv
		heinv_id varchar(13),
		heinvitem_id varchar(13),
		heinv_art	varchar(30)	,
		heinv_mat	varchar(30)	,
		heinv_col	varchar(30)	,
		heinv_coldescr	varchar(30)	,
		heinv_size varchar(10),
		heinv_colnum varchar(2),
		heinv_name	varchar(255)	,
		heinv_priceori	decimal(15,0)	,
		heinv_priceadj	decimal(15,0)	,
		heinv_pricegross	decimal(15,0)	,
		heinv_price	decimal(15,0)	,
		heinv_pricedisc	decimal(15,0)	,
		heinv_pricenett	decimal(15,0)	,
		discflag	varchar(10)	,
		pcp_line	varchar(50)	,
		pcp_gro	varchar(50)	,
		pcp_ctg	varchar(50)	,
		gtype	varchar(10)	,
		gender	varchar(10)	,
		fit	varchar(50)	,
		season_group	varchar(20)	,
		season_id	varchar(10)	,
		deftype_id	varchar(10)	,
		RVID	varchar(30)	,
		RVDT	date	,
		age	int	,
		

		-- from Site
		site_id varchar(30),
		site_name varchar(90),
		site_sqm decimal(12,2),
		site_code varchar(30),
		site_isclose tinyint(1) not null default 0,
		site_isdisabled  tinyint(1) not null default 0,
		location_id varchar(30),
		location_name varchar(90),
		city_id varchar(30),
		area_id varchar(30),
		sitemodel_id varchar(10),
		kalista_site_id varchar(30),

		-- from invCls
		invcls_id varchar(30),
		invcls_name varchar(100),

		-- from Category
		heinvctg_id	varchar(10),
		heinvctg_name	varchar(50)	,
		heinvctg_sizetag	varchar(5)	,
		heinvgro_id	varchar(10),
		heinvgro_name	varchar(30),
		mdflag	varchar(10)	,
		
		lastcost decimal(18,2),
		total_qty int,
		total_value decimal(18,2)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;
	*/

}