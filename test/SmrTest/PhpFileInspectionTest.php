<?php declare(strict_types=1);

namespace SmrTest;

use Overtrue\PHPLint\Linter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class PhpFileInspectionTest extends TestCase {

	public function test_all_files_pass_phplint(): void {
		$paths = [ROOT];
		$excludes = ['vendor'];
		$linter = new Linter($paths, $excludes, warning: true);
		$linter->setProcessLimit(8); // multiprocessing

		// get errors
		$errors = $linter->lint();
		$this->assertEmpty($errors, print_r($errors, true));
	}

}
