<?php declare(strict_types=1);

namespace SmrTest;

use Overtrue\PHPLint\Command\LintCommand;
use Overtrue\PHPLint\Configuration\ConsoleOptionsResolver;
use Overtrue\PHPLint\Event\EventDispatcher;
use Overtrue\PHPLint\Finder;
use Overtrue\PHPLint\Linter;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;

#[CoversNothing]
class PhpFileInspectionTest extends TestCase {

	public function test_all_files_pass_phplint(): void {
		$dispatcher = new EventDispatcher([]);

		$arguments = [
			'path' => [ROOT],
			'--exclude' => ['vendor'],
			'--no-configuration' => true,
			'--warning' => true,
			'--jobs' => 8,
		];
		$command = new LintCommand($dispatcher);
		$input = new ArrayInput($arguments, $command->getDefinition());
		$configResolver = new ConsoleOptionsResolver($input);

		$finder = new Finder($configResolver);
		$linter = new Linter($configResolver, $dispatcher);
		$results = $linter->lintFiles($finder->getFiles());

		$errors = $results->getFailures();
		$this->assertEmpty($errors, print_r($errors, true));
	}

}
