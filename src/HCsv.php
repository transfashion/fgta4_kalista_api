<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

if (!defined('CSV_SEPARATOR')) {
	define('CSV_SEPARATOR', '|');
}


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

	public function Close() : void {
		fclose($this->fp);
	}

}