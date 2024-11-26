<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

final class Customer extends Api {

	const ACCESSTYPE_WHATSAPP = "WA";
	const ACCESSTYPE_EMAIL = "EMAIL";


	public string $Id;
	public string $Name;
	public ?string $Phone;
	public ?string $Email;
	public ?string $Gender;
	public ?string $BirthDate;
	public string $CustAccessId;
	public string $CustAccessType;

	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}


	public static final function GetCustomerByAccess(string $accessid) : ?Customer {
		try {
			$db = Database::GetConnection(Configuration::DB_MAIN);

			$key = new \stdClass();
			$key->custaccess_code = $accessid;

			$cmd = new SqlSelect("mst_custaccess", $key);
			$sql = $cmd->getSqlString();
			$stmt = $db->prepare($sql);

			$params = $cmd->getKeyParameter($key);
			$stmt->execute($params);
			$row = $stmt->fetch();
			
			if (!$row) {
				return null;
			}

			$cust_id = $row['cust_id'];
			$custaccesstype_id = $row['custaccesstype_id'];

			$key = new \stdClass();
			$key->cust_id = $cust_id;

			$cmd = new SqlSelect("mst_cust", $key);
			$sql = $cmd->getSqlString();
			$stmt = $db->prepare($sql);

			$params = $cmd->getKeyParameter($key);
			$stmt->execute($params);
			$row = $stmt->fetch();
	 
			$c = new Customer();
			$c->Id = $row['cust_id'];
			$c->Name = $row['cust_name'];
			$c->Phone = $row['cust_phone'];
			$c->Email = $row['cust_email'];
			$c->Gender = $row['gender_id'];
			$c->BirthDate = $row['cust_birthdate'];
			$c->CustAccessId = $accessid;
			$c->CustAccessType = $custaccesstype_id;

			return $c;		
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private static function getCustAccess(object $db, string $accessid) : ?array {
		try {
			$key = new \stdClass();
			$key->custaccess_code = $accessid;
			
			$cmd = new SqlSelect("mst_custaccess", $key);
			$cmd->bind($db);
			$cmd->execute($key);
			$stmt = $cmd->getPreparedStatement();
			$row = $stmt->fetch() ?? null;
			if (!$row) {
				return null;
			}

			return $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	} 

	private static function getCustByPhone(object $db, string $phone) : ?array {
		try {			
			$key = new \stdClass();
			$key->cust_phone = $phone;

			$cmd = new SqlSelect("mst_cust", $key);
			$cmd->bind($db);
			$cmd->execute($key);
			$stmt = $cmd->getPreparedStatement();
			$row = $stmt->fetch();
			if (!$row) {
				return null;
			}
			return $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private static function getCustByEmail(object $db, string $email) : ?array {
		try {
			$key = new \stdClass();
			$key->cust_email = $email;

			$cmd = new SqlSelect("mst_cust", $key);
			$cmd->bind($db);
			$cmd->execute($key);
			$stmt = $cmd->getPreparedStatement();
			$row = $stmt->fetch();
			if (!$row) {
				return null;
			}
			return $row;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private static function createSimpleCust(object $db, ?string $name, ?string $phone, ?string $email) : object {
		try {
			$obj = new \stdClass;
			$obj->cust_id = uniqid();
			$obj->cust_name = $name;
			$obj->cust_phone = $phone;
			$obj->cust_email = $email;
			$obj->_createby = 'SYSTEM';

			$cmd = new SqlInsert("mst_cust", $obj);
			$cmd->bind($db);
			$cmd->execute($obj);

			return $obj;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	private static function addAccess(object $db, string $cust_id, string $custaccesstype_id, string $accessid) : object {
		try {
			$obj = new \stdClass;
			$obj->custaccess_id = uniqid();
			$obj->cust_id = $cust_id;
			$obj->custaccesstype_id = $custaccesstype_id;
			$obj->custaccess_code =  $accessid;
			$obj->_createby = 'SYSTEM';

			$cmd = new SqlInsert("mst_custaccess", $obj);
			$cmd->bind($db);
			$cmd->execute($obj);

			return $obj;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public static final function CreateNewByAccess(string $accesstype, string $accessid, string $name) : ?Customer {
		try {
			$db = Database::GetConnection(Configuration::DB_MAIN);
			$db->beginTransaction();

			try {
				// cek apakah accessid sudah ada
				$row = self::getCustAccess( $db, $accessid);
				if ($row!=null) {
					throw new \Exception("access '$accessid' already exist");
				}
			
				// cek data mst_user berdasarkan accesstype
				$cust = null;
				$phone = null;
				$email = null;
				if ($accesstype==self::ACCESSTYPE_WHATSAPP) {
					$phone = $accessid;
					$cust = self::getCustByPhone($db,  $phone);
				} else if ($accesstype==self::ACCESSTYPE_EMAIL) {
					$email = $accessid;
					$cust = self::getCustByEmail($db, $email);
				} else {
					throw new \Exception("Access type is invalid");
				}

				if ($cust!=null) {
					throw new \Exception("user for '$accessid' already exist");
				}

				// buat dulu master customer di mst_cust
				$cust = self::createSimpleCust($db, $name, $phone, $email);

				// tambahkan akses
				$cust_id = $cust->cust_id;
				self::addAccess($db, $cust_id, $accesstype, $accessid);

				$db->commit();
			} catch (\Exception $ex) {
				$db->rollBack();
				throw $ex;
			}


			// ambil data customer
			$c = self::GetCustomerByAccess($accessid);
			return $c;		
		} catch (\Exception $ex) {
			throw $ex;
		}	
	
	}

}