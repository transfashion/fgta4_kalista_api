<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

abstract class Api {
	abstract public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void;


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
}	