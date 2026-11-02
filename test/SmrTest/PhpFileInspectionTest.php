<?php declare(strict_types=1);

namespace SmrTest;

use Exception;
use Overtrue\PHPLint\Finder;
use Overtrue\PHPLint\Linter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
class PhpFileInspectionTest extends TestCase {

	public function test_all_files_pass_phplint(): void {
		$finder = new Finder(
			paths: [ROOT],
			excludes: ['vendor'],
			fileExtensions: ['php'],
		);
		$linter = new Linter(processLimit: 8, showWarning: true);
		$results = $linter->lintFiles($finder->getFiles());

		// PHPLint doesn't do its own sanity checking, so we must do it here.
		// https://github.com/overtrue/phplint/issues/183
		if (count($results) === 0) {
			throw new Exception('Failed to find any files to lint.');
		}

		$errors = $results->getFailures();
		self::assertEmpty($errors, print_r($errors, true));
	}

}
