<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

use finfo;

final class Whatsapp extends Api {
	const LOGIN_INTENT = '#login-via-whatsapp';


	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		// tidak perlu melakukan verifikasi untuk consume Api ini
	}

	private function parseMessage(string $message) : array {
		try {
			// parse message
			// contoh format message:
			// Hai Transfashion, Saya ingin #login-via-whatsapp ke website transfashion.id [ref:cde19f67e13f86a5172695473aafaa2f]	
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
	 * ini di hit dari qiscus, saat ada customer send message login ke channel Transfashion
	 */
	public final function CustomerLogin(array $payload) : array {
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
	
	
			Log::info("[$phone_number] receive message: $message");
			if ($intent!=self::LOGIN_INTENT) {
				$errmsg = Log::warning("[$phone_number] intent data yg dikirim '$intent' tidak sesuai");
				throw new \Exception($errmsg, 400);
			}
	
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
				Log::warning("[$phone_number] message is not valid.");
			} else {
				$kalista_sessid = $metadata['ref'];
				session_id($kalista_sessid);
				session_start();
				if (array_key_exists('external_callback_url', $_SESSION)) {
					$cust=Customer::GetCustomerByAccess($phone_number);
					if (!$cust) {
						Log::info("[$phone_number] is new customer, create new customer");
						$cust=Customer::CreateNewByAccess(Customer::ACCESSTYPE_WHATSAPP, $phone_number, $from_name);
					} else {
						Log::info("[$phone_number] number found is customer database.");
					}

					if ($cust!=null) {
						$external_callback_url = $_SESSION['external_callback_url'];
						$_SESSION['cust_id'] = $cust->Id;
						$_SESSION['cust_name'] = $cust->Name;
						$_SESSION['cust_phone'] = $cust->Phone;
						$_SESSION['cust_email'] = $cust->Email;
						$_SESSION['gender_id'] = $cust->Gender;
						$_SESSION['cust_birthdate'] = $cust->BirthDate;
						$_SESSION['custaccess_code'] = $cust->CustAccessId;
						$_SESSION['custaccesstype_id'] = $cust->CustAccessType;
						$replyMessage = "Login success, please back to previous page, or you can click $external_callback_url?id=$kalista_sessid (link valid for 3 minutes)";
						Log::info("[$phone_number] Login success");
					} else {
						$errmsg = "error while get/insert new data customer, returned error empty data";
						Log::error("[$phone_number] $errmsg");
						$replyMessage = $errmsg;
					}
				} else {
					$replyMessage = "login via whatssapp fail, please try again later";
					Log::warning("[$phone_number] Login fail, cek session kalista: $kalista_sessid");
					Log::debug(print_r($_SESSION, true));
				}
			} 
	
			$qiscusConfig = Configuration::Get('Qiscus');
			$url = $qiscusConfig['Url'];
			$appcode = $qiscusConfig['AppCode'];
			$secret = $qiscusConfig['AppSecret'];
			$sender = $qiscusConfig['Sender'];
			Qiscus::Setup($url, $appcode, $secret, $sender);
			Qiscus::SendText($room_id, $replyMessage);
			Qiscus::Resolve($room_id);

			$result = [
				"success" => true,
				"message" => $replyMessage
			];

			return $result;
		} catch (\Exception $ex) {
			$code = $ex->getCode();
			Log::error($ex->getMessage(), $code);
		}
	}
}