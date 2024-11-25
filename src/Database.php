<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class Database {
	private static array $_connections = [];


	public final static function Connect(string $name, array $config) : void {
		try {
			if (!array_key_exists($name, self::$_connections)) {
				$DSN = $config['DSN'];
				$user = $config['user'];
				$pass = $config['pass'];

				$db = new \PDO(
					$DSN, 
					$user, 
					$pass , 
					[
						\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
						\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
						\PDO::ATTR_PERSISTENT=>true		
					]
				);

				self::$_connections[$name] = [
					'db' => $db,
					'DSN' => $DSN,
					'user' => $user,
					'pass' => $pass
				];
			} else {
				$conn = self::$_connections[$name];
				if ($conn['DSN']!=$config['DSN'] || $conn['user']!=$config['user']) {
					throw new \Exception("connection '$name' is already used");
				}
			}
			
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public final static function GetConnection(string $name) : \PDO {
		if (!array_key_exists($name, self::$_connections)) {
			throw new \Exception("connection '$name' is not exists");
		}

		$conn = self::$_connections[$name];
		return $conn['db'];
	}
}