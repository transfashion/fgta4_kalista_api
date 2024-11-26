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




	protected static function DoSimpleVerification(string $jsonTextData, array $headers) : void {
		try {
			// cek timestamp
			// jika timestamp sudah terlalu lama, throw expired request

			// verify tx
			// jika appid dan tx sudah ada di db, throw duplicate execution

			// cek code verifier
			$appid = array_key_exists('App-Id', $headers) ?  $headers['App-Id'] : '';
			$secret = array_key_exists('App-Secret', $headers) ? $headers['App-Secret'] : '';
			$codeVerifier = array_key_exists('Code-Verifier', $headers) ? $headers['Code-Verifier'] : '';
			if (!Api::IsValidCodeVerifier($codeVerifier, $appid, $secret, $jsonTextData)) {
				throw new \Exception("your data authentication is invalid", 403);
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

}	