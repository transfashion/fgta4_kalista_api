<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class Qiscus {

	private static string $_qiscus_endpoint;
	private static string $_qiscus_sender;
	private static string $_qiscus_secret;


	public static function Setup(string $endpoint, string $sender, string $secret) : void {
		self::$_qiscus_endpoint = $endpoint;
		self::$_qiscus_sender = $sender;
		self::$_qiscus_secret = $secret;
	}

	public static function Resolve(string $room_id) : bool {
		try {


			return true;
		} catch (\Exception) {
			return false;
		}
	}


	public static function SendText(string $room_id, string $text) : bool {
		try {

			$data = array(
				"sender_email" => self::$_qiscus_sender, 
				"message" => $text,
				"type" => "text",
				"room_id" => $room_id
			);
			$postdata = json_encode($data);

			$ch = curl_init(self::$_qiscus_endpoint);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLINFO_HEADER_OUT, true);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
			
			// Set HTTP Header for POST request
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'QISCUS_SDK_SECRET: ' . self::$_qiscus_secret,
				'Content-Length: ' . strlen($postdata))
			);

			$result = curl_exec($ch);
			Log::info($result);

			return true;
		} catch (\Exception) {
			return false;
		}
	}

}
