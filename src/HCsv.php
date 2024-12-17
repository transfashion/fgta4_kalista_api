<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

if (!defined('CSV_SEPARATOR')) {
	define('CSV_SEPARATOR', '|');
}

use AgungDhewe\PhpSqlUtil\SqlInsert;
use AgungDhewe\PhpSqlUtil\SqlUpdate;

final class HCsv {
	private mixed $fp;
	private string $filepath;
	private array $header = [];

	public static final function Open(string $path) : mixed {
		$fp = fopen($path, 'r');
		$csv = new self($fp, $path);
		return $csv;
	}	


	function __construct(mixed $resource, string $filepath) {
		$this->fp = $resource;
		$this->filepath = $filepath;
		if (!$this->readheader()) {
			throw new \Exception("File '$this->filepath' is empty");
		}

	}

	private function readheader() : bool {
		$line = fgets($this->fp, 5*1024);

		if (empty($line)) {
			return false;
		}
		$headdata = explode(CSV_SEPARATOR, trim($line));
		
		$i = 0;
		foreach ($headdata as $key) {
			$this->header["{$i}"] = trim($key);
			$i++;
		}

		return true;
	}


	public function readline() : bool|array {
		try {
			if (!feof($this->fp)) {
				$line = fgets($this->fp, 5*1024);
				if (empty($line)) {
					return false;
				}


				$rowdata = explode(CSV_SEPARATOR, trim($line));
				$columncount = count($rowdata);

				$data = array();
				foreach ($this->header as $key=>$columnname) {
					if (empty($columnname)) {
						continue;
					}	

					$index = (int)$key;
					if ($index<$columncount) {
						$value = $rowdata[$index];
					} else {
						$value = "";
					}

					$data[$columnname] = $value;
				}

				return $data;
			} else {
				return false;
			}
		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public function syncToTable(\PDO $db, string $tablename, string $primarykey, callable $fnCreateRowObject) : void {
		try {

			while ($row=$this->readline()) {
				$obj = $fnCreateRowObject($row);

				if (!isset($stmt_cek)) {
					$query = "select $primarykey, md5checksum from $tablename where $primarykey=:$primarykey";
					$stmt = $db->prepare($query);
					$stmt_cek = $stmt;
				}

				$pkvalue = $obj->{$primarykey};

				$stmt = $stmt_cek;
				$stmt->execute([":$primarykey"=>$pkvalue]);
				$rowcek = $stmt->fetch();
				if ($rowcek==null) {
					// insert
					if (!isset($cmd_insert)) {
						$cmd = new SqlInsert($tablename, $obj);
						$cmd->bind($db);
						$cmd_insert = $cmd;
					}

					Log::info("inserting into $tablename : $pkvalue");
					$cmd = $cmd_insert;
					$cmd->execute($obj);


				} else {
					if ($row['md5checksum']!=$rowcek['md5checksum']) {
						// update
						if (!isset($cmd_update)) {
							$cmd = new SqlUpdate($tablename, $obj, [$primarykey]);
							$cmd->bind($db);
							$cmd_update = $cmd;
						}

						Log::info("updating $tablename : $pkvalue");
						$cmd = $cmd_update;
						$cmd->execute($obj);

						
					}
				}
			}

		} catch (\Exception $ex) {
			throw $ex;
		}
	}

	public function Close() : void {
		fclose($this->fp);
	}

}