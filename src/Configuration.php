<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

use Exception;

final class Configuration {
	const SPARATOR = ".";
	const DB_MAIN = "DbMain";	

	const DB_PARAM = [
		\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
		\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
		\PDO::ATTR_PERSISTENT=>true
	];

	private static array $_config;
	private static array $_usedConfig;
	private static string $_rootDir;
	private static array $_allowedApps = [];

	public static function Set(array $config) : void {
		self::$_config = $config;
	}

	public static function Get(?string $keypath = null) : mixed {
		try {
			if ($keypath!==null) {
				$value = self::getValueByPath(self::$_config, $keypath);
				return $value;
			} else {
				return self::$_config;
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public static function GetUsedConfig(string $name) : string {
		if (!array_key_exists($name, self::$_usedConfig)) {
			throw new \Exception("Config '$name' tidak ditemukan");
		}
		return self::$_usedConfig[$name];
	}

	public static function UseConfig(array $usedconfig) : void {
		self::$_usedConfig = $usedconfig;
	}

	private static function getValueByPath(array $array, string $path, ?string $separator = self::SPARATOR) : mixed {
		$keys = explode($separator, $path);
		foreach ($keys as $key) {
			if (!isset($array[$key])) {
				return null; // Kunci tidak ditemukan
			}
			$array = $array[$key]; // Melangkah lebih dalam ke array
		}
		return $array;
	}


	public static function setRootDir($dir) : void {
		if (!defined('__ROOT_DIR__')) {
			define('__ROOT_DIR__', $dir);
		}

		self::$_rootDir = $dir;


		
	}

	public static function getRootDir() : string {
		return self::$_rootDir;
	}



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