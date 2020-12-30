<?php declare(strict_types=1);

namespace SmrTest;

use PHPUnit\Framework\TestCase;

class PhpFileInspectionTest extends TestCase {

	public function test_all_files_use_strict_type() {
		$exit_code = 1;
		$output = [];
		exec(ROOT . 'test/strict_types.sh', $output, $exit_code);
		$this->assertSame(0, $exit_code, join("\n", $output));
		$this->assertEquals(end($output), 'Success! No strict_type errors.');
	}

	public function test_all_files_pass_phplint() {
		$exit_code = 1;
		$output = [];
		exec(ROOT . 'test/phplint.sh', $output, $exit_code);
		$this->assertSame(0, $exit_code, join("\n", $output));
		$this->assertEquals(end($output), 'Success! No linting errors.');
	}

}
