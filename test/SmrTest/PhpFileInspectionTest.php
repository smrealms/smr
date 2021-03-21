<?php declare(strict_types=1);

namespace SmrTest;

use PHPUnit\Framework\TestCase;
use Overtrue\PHPLint\Linter;

/**
 * @coversNothing
 */
class PhpFileInspectionTest extends TestCase {

	public function test_all_files_use_strict_type() {
		$exit_code = 1;
		$output = [];
		exec(ROOT . 'test/strict_types.sh', $output, $exit_code);
		$this->assertSame(0, $exit_code, join("\n", $output));
		$this->assertEquals('Success! No strict_type errors.', end($output));
	}

	public function test_all_files_pass_phplint() {
		$paths = [ROOT];
		$excludes = ['vendor'];
		$linter = new Linter($paths, $excludes, warning: true);
		$linter->setProcessLimit(8); // multiprocessing

		// get errors
		$errors = $linter->lint();
		$this->assertEmpty($errors, print_r($errors, true));
	}

}
