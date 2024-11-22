<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


class Session extends Api {

	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		try {
			$appid = $headers['App-Id'];
			$secret = $headers['App-Secret'];
			$codeVerifier = $headers['Code-Verifier'];

			if (!Api::IsValidCodeVerifier($codeVerifier, $appid, $secret, $jsonTextData)) {
				throw new \Exception("your data authentication is invalid", 403);
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	
	/**
	 * @ApiMethod
	 */
	public function RegisterExternalSession(string $sessid) : array {
		$result = null;
		$success = false;
		$errmessage = '';

		// buat session yang singkat
		$lifetime = 3 * 60; // 3 menit
		ini_set('session.gc_maxlifetime',  $lifetime); 

	
		try {
			session_start();
			$kalista_sessid = session_id();
			$_SESSION['external_session_id'] = $sessid;
			$result = [
				'kalista_sessid' => $kalista_sessid
			];
			$success = true;
		} catch (\Exception $ex) {
			$errmessage = $ex->getMessage();
			
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
}