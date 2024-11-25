<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


final class TestHit extends Api {

	public function VerifyRequest(string $functionname, string $jsonTextData, array $headers) : void {
	}

	/**
	 * @ApiMethod
	 * 
	 * testing apakah api bisa di hit dari luar
	 * $host_url/api/Transfashion/KalistaApi/TestHit/TestMethod
	 */
	public final function TestMethod(string $testParam) : array {
		Log::debug("TestHit::TestMethod() executed");
		return [
			"result" => "you've send paramter \$testParam : '$testParam'"
		];
	}

	/**
	 * @ApiMethod
	*/
	public final function TestQiscusRobolabs(array $payload) : bool {
		$rootDir = Configuration::getRootDir();
		$outputfilepath = join(DIRECTORY_SEPARATOR, [$rootDir, 'output', 'testqiscusrobolabs.txt']);
		$jsonData = print_r($payload, true);
	
		$fp = fopen($outputfilepath, "w");
		fputs($fp, $jsonData);
		fclose($fp);

		Log::debug("TestHit::TestQiscusRobolabs() executed");
		Log::info($jsonData);

		return true;
	}

}