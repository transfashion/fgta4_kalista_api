<?php declare(strict_types=1);
namespace Transfashion\KalistaApi\TransBrowserSales;

use AgungDhewe\PhpSqlUtil\SqlSelect;
use AgungDhewe\PhpSqlUtil\SqlInsert;

final class BonItem  {

	public static final function Save(\PDO $db, string $bon_id, array $itemrows) : bool {
		try {
			foreach ($itemrows as $row) {
				

			}
			return true;
		} catch (\Exception $ex) {
			throw $ex;
		}

	}

}