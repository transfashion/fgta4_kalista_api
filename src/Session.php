<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class Session extends Api {

	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	/**
	 * @ApiMethod
	 */
	public function RegisterExternalSession(string $sessid, string $callback_url) : array {
		$external_session_id = $sessid;

		$result = null;
		$success = false;
		$errmessage = '';

		// buat session yang singkat
		$lifetime = 3 * 60; // 3 menit
		ini_set('session.gc_maxlifetime',  $lifetime); 

	
		try {

			session_start();
			$kalista_sessid = session_id();
			$_SESSION['external_session_id'] = $external_session_id;
			$_SESSION['external_callback_url'] = $callback_url;
			$_SESSION['expired'] = time() + $lifetime;
			$result = [
				'kalista_sessid' => $kalista_sessid
			];

			Log::info("Create New Session $kalista_sessid from external $external_session_id with url $callback_url");
			$success = true;
		} catch (\Exception $ex) {
			$errmessage = $ex->getMessage();
			Log::error($errmessage);

			$success = false;
			$result = null;
		} finally {
			$res = [
				'success' => $success,
				'errormessage' => $errmessage,
				'result' => $result,

			];
			return $res;
		}
	}

	/**
	 * @ApiMethod
	 */
	public function RenewSession(string $sessid, string $external_sessid, string $expired) : array {
		try {
			$lifetime = $expired - time();
			ini_set('session.gc_maxlifetime',  $lifetime); 
			session_id($sessid);
			session_start();
			session_regenerate_id(true);
			$kalista_sessid = session_id();

	
			
			$result = [
				'success' => true,
				'errormessage' => '',
				'kalista_sessid' => $kalista_sessid
			];
		} catch (\Exception $ex) {
			$result = [
				'success' => false,
				'errormessage' => $ex->getMessage(),
				'kalista_sessid' => null
			];
			return $result;
		}
	}


	// /**
	//  * @ApiMethod
	//  */
	// public function RegisterLoginSession(string $old_kalista_sessid, string $external_sessid) : array {
	// 	try {

			
	// 		// create new kalista sessionid 
	// 		$new_kalista_sessid = '';

	// 		$result = [
	// 			'success' => true,
	// 			'errormessage' => '',
	// 			'kalista_sessid' => $new_kalista_sessid
	// 		];
	// 	} catch (\Exception $ex) {
	// 		$errmsg = $ex->getMessage();
	// 		Log::info($errmsg);
	// 		$result = [
	// 			'success' => false,
	// 			'errormessage' => $errmsg,
	// 			'kalista_sessid' => null
	// 		];

	// 	} finally {
	// 		return $result;
	// 	}
	// }


	/**
	 * @ApiMethod
	 */
	public function SessionLogout(string $sessid) : array {
		$kalista_sessid = $sessid;
		session_id($kalista_sessid);
		session_start();
		session_unset();
		session_destroy();
		Log::info("session destroyed: $kalista_sessid");
		return [
			'success' => true
		];
	}



	/**
	 * @ApiMethod
	 * 
	 */
	public final function GetCustomerLogin(string $sessid) : array { 
		$lifetime = 3 * 60; // 3 menit
		ini_set('session.gc_maxlifetime',  $lifetime); 

		session_id($sessid);
		session_start();

		$result = [];


		try {
			if (!array_key_exists('cust_id', $_SESSION)) {
				// notes: jangan destroy session di sini, soalnya masih dipakai (ini hanya untuk cek)
				Log::info("[$sessid] cust_id is not in session data, login is expired or not logged in yet");
				throw new \Exception("Login session is expired, or user is not logged in yet");
			}

			// cek jika session sudah expired 
			$expired = array_key_exists('expired', $_SESSION) ? $_SESSION['expired'] : null;
			$now = time();
			if ($expired==null || $now>$expired) {
				session_unset();
				session_destroy();
				Log::info("[$sessid] session is expired");
				throw new \Exception("Login session is expired, or user is not logged in yet");
			}


			$customer = [
				'id' => $_SESSION['cust_id'] ?? null,
				'name' => $_SESSION['cust_name'] ?? null,
				'phone' => array_key_exists('cust_phone', $_SESSION) ? $_SESSION['cust_phone'] : null,
				'email' => array_key_exists('var', $_SESSION) ? $_SESSION['cust_email'] : null,
				'gender' => array_key_exists('gender_id', $_SESSION) ? $_SESSION['gender_id'] : null,
				'birthdate' => array_key_exists('cust_birthdate', $_SESSION) ? $_SESSION['cust_birthdate'] : null,
				'custaccess_code' => array_key_exists('custaccess_code', $_SESSION) ? $_SESSION['custaccess_code'] : null,
				'custaccesstype_id' => array_key_exists('custaccesstype_id', $_SESSION) ? $_SESSION['custaccesstype_id'] : null,
			];

			$cust_id = $customer['id'];
			$cust_name = $customer['name'];
			Log::info("[$sessid] user is $cust_id $cust_name");
			$result = [
				'success' => true,
				'errormessage' => '',
				'data' => $customer
			];
		} catch (\Exception $ex) {
			$errmsg = $ex->getMessage();
			Log::info("[$sessid] $errmsg");
			$result = [
				'success' => false,
				'errormessage' => $errmsg,
				'data' => null
			];

		} finally {
			return $result;
		}
	}



}