<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

use Exception;

use AgungDhewe\Setingan\Config;


final class Configuration extends Config {
	const DB_MAIN = "DbMain";	

	const DB_PARAM = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_PERSISTENT=>true
	];

	private static array $_allowedApps = [];


	public static function addAllowedApp(string $appid, string $appsecret) : void {
		if (!array_key_exists($appid, self::$_allowedApps)) {
			self::$_allowedApps[$appid] = ['secret'=>$appsecret];
		} else {
			throw new Exception("AppID already exist", 500);
		}
	}

	public static function isValidApp(string $appid, string $secret) : bool {
		if (!array_key_exists($appid, self::$_allowedApps)) {
			return false;
		}

		$app = self::$_allowedApps[$appid];
		if ($secret != $app['secret']) {
			return false;
		}

		return true;
	}
}