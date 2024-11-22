<?php declare(strict_types=1);
namespace Transfashion\KalistaApi;

abstract class Api {
	abstract public function VerifyRequest(string $functionname, string $headers, string $jsonTextData) : bool;
}	