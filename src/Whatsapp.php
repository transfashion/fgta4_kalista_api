<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

use finfo;

final class Whatsapp extends Api {
	const LOGIN_INTENT = '#login-via-whatsapp';


	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
	}

	private function parseMessage(string $message) : array {
		try {
			// parse message
			$cleanedInput = str_replace("\n", " ", $message);
			$pattern = '/#([\w-]+)|\[(.*?)\]/';
			preg_match_all($pattern, $cleanedInput, $matches);
			
			$messageIntent = $matches[0][0] ?? null;
			$metadataString = $matches[0][1] ?? null;
			$metadata = [
				'intent' => $messageIntent,
			];
			if (!empty($metadataString)) {
				preg_match_all('/(\w+):([\w-]+)/', $metadataString, $metaMatches, PREG_SET_ORDER);
				foreach ($metaMatches as $meta) {
					$metadata[$meta[1]] = $meta[2];
				}
			}
			return $metadata;
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	/**
	 * @ApiMethod
	 * 
	 */
	public final function GetCustomerLoginSession(string $sessid) : array { 
		session_id($sessid);
		session_start();

		$result = [];


		try {
			if (!array_key_exists('cust_id', $_SESSION)) {
				throw new \Exception("No user login information, or user is not logged in yet");
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

			$result = [
				'success' => true,
				'errormessage' => '',
				'data' => $customer
			];
		} catch (\Exception $ex) {
			$result = [
				'success' => false,
				'errormessage' => $ex->getMessage(),
				'data' => null
			];

		} finally {
			return $result;
		}
	}

	/**
	 * @ApiMethod
	 * 
	 * ini di hit dari qiscus, saat ada customer send message login ke channel Transfashion
	 */
	public final function CustomerLogin(array $payload) : void {
		try {

			$cfgkey = Configuration::GetUsedConfig(Configuration::DB_MAIN);
			$dbconfig = Configuration::Get($cfgkey);
			Database::Connect(Configuration::DB_MAIN, $dbconfig);
			Log::info('Databse Connected!');


			$intent = array_key_exists('intent', $payload) ? $payload['intent'] : '';
			$message =  array_key_exists('message', $payload) ? $payload['message'] : '';
			$phone_number =  array_key_exists('phone_number', $payload) ? $payload['phone_number'] : '';
			$from_name =  array_key_exists('from_name', $payload) ? $payload['from_name'] : '';
			$room_id =  array_key_exists('room_id', $payload) ? $payload['room_id'] : '';
	
	
			Log::info("receive message: $message");
			if ($intent!=self::LOGIN_INTENT) {
				$errmsg = Log::warning("intent data yg dikirim '$intent' tidak sesuai");
				throw new \Exception($errmsg, 400);
			}
	
			$loginSuccess = false;
			$isValidMessage = false;
			$metadata = $this->parseMessage($message);

			if ($intent==$metadata['intent']) {
				if (array_key_exists('ref', $metadata)) {
					$isValidMessage = true;
				}
			}


			$replyMessage = '';
			if (!$isValidMessage) {
				$replyMessage = "Message Format for login is not valid";
			} else {
				$kalista_sessid = $metadata['ref'];
				session_id($kalista_sessid);
				session_start();
				if (array_key_exists('external_callback_url', $_SESSION)) {
					$cust=Customer::GetCustomerByAccess($phone_number);
					if (!$cust) {
						$cust=Customer::CreateNew($phone_number, $from_name);
					}

					$external_callback_url = $_SESSION['external_callback_url'];
					$_SESSION['cust_id'] = $cust->Id;
					$_SESSION['cust_name'] = $cust->Name;
					$_SESSION['cust_phone'] = $cust->Phone;
					$_SESSION['cust_email'] = $cust->Email;
					$_SESSION['gender_id'] = $cust->Gender;
					$_SESSION['cust_birthdate'] = $cust->BirthDate;
					$_SESSION['custaccess_code'] = $cust->CustAccessId;
					$_SESSION['custaccesstype_id'] = $cust->CustAccessType;
					$loginSuccess=true;
					$replyMessage = "Login success, please back to previous page, or you can click $external_callback_url?id=$kalista_sessid ";
				} else {
					$replyMessage = "login via whatssapp fail, please try again later";
				}
			} 
	
			
			if (!$isValidMessage) {
				
			} else if (!$loginSuccess) {
	
			} else {
	
			}
	

		} catch (\Exception $ex) {
			$code = $ex->getCode();
			Log::error($ex->getMessage(), $code);
		}
	}
}