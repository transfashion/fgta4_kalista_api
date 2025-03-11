<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class Log {

	private static string $_logoutput;
	private static string $_request;
	private static int $_maxsize;

	public final static function setOutput(string $logoutput, int $logmaxsize) : void {
		try {
			if (!is_file($logoutput)) {
				throw new \Exception("Output log file '$logoutput' tidak ditemukan");
			}
			self::$_logoutput = $logoutput;
			self::$_maxsize = $logmaxsize;


			$logSize = filesize(self::$_logoutput);
			if ($logSize > self::$_maxsize) {
				file_put_contents(self::$_logoutput, "");
			}		

		} catch (\Exception $ex) {
			throw $ex;
		}
	}


	public final static function setRequest(string $request) : void {
		self::$_request = $request;
		$entry = join("\t", [date("Y-m-d H:i:s"), "REQUEST", $request]);
		file_put_contents(self::$_logoutput, $entry . "\n", FILE_APPEND);
	}

	private static function writeTextToOutputFile(string $type, string $text, ?string $reference=null) : string {
		$text = str_replace("\n", "", $text);

		if (!isset(self::$_request)) {
			self::$_request = '';
		}

		if ($reference) {
			$entry = join("\t", [date("Y-m-d H:i:s"), $type, $text, self::$_request, $reference]);
		} else {
			$entry = join("\t", [date("Y-m-d H:i:s"), $type, $text, self::$_request]);
		}
		

		file_put_contents(self::$_logoutput, $entry . "\n", FILE_APPEND);
		return $text;
	} 

	private static function getCallerReference() : string {
		$trace = debug_backtrace();
		$caller = $trace[1];
		$reference = $caller['file'] . ":" . $caller['line'];
		return $reference;
	}

	public final static function info(string $entry) : string {
		$ret = self::writeTextToOutputFile("INFO", $entry);
		return $ret;
	}

	public final static function error(string $entry) : string {
		$reference = self::getCallerReference();
		$ret = self::writeTextToOutputFile("ERROR", $entry, $reference);
		return $ret;
	}

	public final static function warning(string $entry) : string {
		$reference = self::getCallerReference();
		$ret = self::writeTextToOutputFile("WARNING", $entry, $reference);
		return $ret;
	}

	public final static function debug(string $entry) : string {
		$reference = self::getCallerReference();
		$ret = self::writeTextToOutputFile("DEBUG", $entry, $reference);
		return $ret;
	}

	public final static function access(string $entry) : string {
		$ret = self::writeTextToOutputFile("ACCESS", $entry);
		return $ret;
	}

	public final static function print(mixed $o) : void {
		if (is_object($o) | is_array($o)) {
			print_r($o);
		} else {
			echo $o;
			echo "\n";
		}
	}


	public final static function dump(mixed $entry) : string {
		$var = print_r($entry, true);
		$ret = self::writeTextToOutputFile("DUMP", $var);
		return $ret;
	}

}