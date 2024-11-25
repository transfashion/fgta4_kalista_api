<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

abstract class Api {
	abstract public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void;


	public function VerifyApplication(string $app_id, string $app_secret) : void {
		if (!Configuration::isValidApp($app_id, $app_secret)) {
			$errmsg = Log::access("Not Allowed invalid applications/secret: '$app_id'");
			throw new \Exception($errmsg, 403);
		}
	}


	public static function IsValidCodeVerifier(string $codeVerifier, string $appid, string $secret, string $data) : bool {
		try {
			$calculatedCodeVerifier = hash_hmac('sha256', join(":", [$appid, $data]), $secret);
			if ($codeVerifier!=$calculatedCodeVerifier) {
				return false;
			}

			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}
	} 


	/**
	 * untuk eksekusi api internal
	 */
	public static function Hit($endpoint, $param, $headers) : void {

	}
}	