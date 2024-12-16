<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransbrowserInv;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

use Transfashion\KalistaApi\Api;
use Transfashion\KalistaApi\Configuration;
use Transfashion\KalistaApi\Database;
use Transfashion\KalistaApi\Log;

use \ZipArchive;

final class Data extends Api {
	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
		self::DoSimpleVerification($jsonTextData, $headers);
	}

	/**
	 * @ApiMethod
	 */
	public function Upload(string $filename, string $content) : array {
		$success = false;
		$message = "";
		
		try {

			$rootDir = Configuration::GetRootDir();
			$fileZip = join(DIRECTORY_SEPARATOR, [$rootDir, 'data', $filename]);

			$fp = fopen($fileZip, "w");
			$data = base64_decode($content);
			fwrite($fp, $data);
			fclose($fp);

			$extractPath = join(DIRECTORY_SEPARATOR, [$rootDir, 'data']);
			$zip = new ZipArchive;


			if ($zip->open($fileZip) === TRUE) {
				if (!$zip->extractTo($extractPath)) {
					throw new \Exception("cannot extract file '$filename'");
				}
				$zip->close();
			} else {
				throw new \Exception("cannot open zipfile '$filename'");
			}

			unlink($fileZip);

			$success = true;
		} catch (\Exception $ex) {
			$success = false;
			$message = $ex->getMessage();
		} finally {
			return [
				"success" => $success,
				"message" => $message
			];
		}
	}


	/**
	 * @ApiMethod
	 */
	public function Process() : array {
		$success = false;
		$message = "";

		try {


			$success = true;
		} catch (\Exception $ex) {
			$success = false;
			$message = $ex->getMessage();
		} finally {
			return [
				"success" => $success,
				"message" => $message
			];
		}

	}

}


