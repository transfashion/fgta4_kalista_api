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

	public static final function CreateNewByAccess(string $accesstype, string $accessid, string $name) : Customer {
		try {
			$db = Database::GetConnection(Configuration::DB_MAIN);

			// buat dulu master customer di mst_cust
			$obj = new \stdClass;
			$obj->cust_id = uniqid();
			$obj->cust_name = $name;
			$obj->_createby = 'SYSTEM';

			if ($accesstype==self::ACCESSTYPE_WHATSAPP) {
				$obj->cust_phone = $accessid;
			} else if ($accesstype==self::ACCESSTYPE_EMAIL) {
				$obj->cust_email = $accessid;
			} else {
				throw new \Exception("Access type is invalid");
			}

			$cmd = new SqlInsert("mst_cust", $obj);
			$sql = $cmd->getSqlString();
			$stmt = $db->prepare($sql);


			
		} catch (\Exception $ex) {
			throw $ex;
		}	


		$c = new Customer();
		return $c;		
	}

}