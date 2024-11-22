<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;


class TestHit extends Api {

	/**
	 * @ApiMethod
	 */
	public function TestMethod(string $testParam) : array {
		return [
			"result" => "you've send paramter \$testParam : '$testParam'"
		];
	}

}