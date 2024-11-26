<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class Qiscus {
	private static string $_Url;
	private static string $_AppCode;
	private static string $_AppSecret;
	private static string $_Sender;


	public static function Setup(string $url,  string $pppcode, string $appsecret, string $sender) : void {
		self::$_Url = $url;
		self::$_AppCode = $pppcode;
		self::$_AppSecret = $appsecret;
		self::$_Sender = $sender;
	}

	public static function Resolve(string $room_id) : bool {
		try {

			$endpoint =  join("/", [self::$_Url, 'api/v1/admin/service/mark_as_resolved']);
			$msg = "resolve by server";
			$data = [
				"room_id" => $room_id,
				"notes" => urlencode($msg)
			];

			$postdata = json_encode($data);
			$response = self::post($endpoint, $postdata);
			$result = json_decode($response);

			if (json_last_error()!==JSON_ERROR_NONE) {
				Log::error($result);
				$errmsg = Log::error(json_last_error_msg());
				throw new \Exception($errmsg);
			}
			return true;
		} catch (\Exception) {
			return false;
		}
	}


	public static function SendText(string $room_id, string $text) : bool {
		try {

			$endpoint = join("/", [self::$_Url, self::$_AppCode, 'bot']);
			$data = [
				"sender_email" => self::$_Sender, 
				"message" => $text,
				"type" => "text",
				"room_id" => $room_id
			];
			$postdata = json_encode($data);
			$response = self::post($endpoint, $postdata);

			if ($response=="ok") {
				return true;
			} else {
				$errmsg = Log::error($response);
				throw new \Exception($errmsg);
			}
		} catch (\Exception) {
			return false;
		}
	}


	public static function post(string $endpoint, string $postdata) : string {
		$ch = curl_init($endpoint);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		
		// Set HTTP Header for POST request
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Qiscus-App-Id: ' . self::$_AppCode,
			'Qiscus-Secret-Key: ' . self::$_AppSecret, 
			'QISCUS_SDK_SECRET: ' . self::$_AppSecret,
			'Content-Length: ' . strlen($postdata))
		);

		$result = curl_exec($ch);
		return $result;
	}

}

