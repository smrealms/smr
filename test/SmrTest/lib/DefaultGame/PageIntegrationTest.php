<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Exception;
use Page;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Session;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest
 * since we are not (or should not be!) writing any data.
 *
 * @covers Page
 */
class PageIntegrationTest extends TestCase {

	protected function setUp(): void {
		// Reset the DI container for each test to ensure independence.
		DiContainer::initialize(false);
	}

	/**
	 * Insert a mock Session into the DI container to return the input $var
	 * when getCurrentVar is called on it.
	 *
	 * @param array<string, mixed> $var
	 */
	private function setVar(array $var): void {
		$page = Page::create('test', $var);
		$session = $this->createMock(Session::class);
		$session
			->method('getCurrentVar')
			->willReturn($page);
		DiContainer::getContainer()->set(Session::class, $session);
	}

	//------------------------------------------------------------------------

	public function test_create(): void {
		// Test create with only a file argument
		$page = Page::create('test_file');
		self::assertSame('test_file', $page->file);
		self::assertSame([], $page->getArrayCopy());
	}

	public function test_create_with_data(): void {
		// Test create with data as an array
		$data = ['extra' => 'data'];
		$page1 = Page::create('file1', $data);
		// Check that the expected keys of the ArrayObject are set
		self::assertSame($data, $page1->getArrayCopy());

		// Test create with data as a Page object
		$page2 = Page::create('file2', $page1);
		// Check that the expected keys of the ArrayObject are set
		self::assertSame($data, $page2->getArrayCopy());
		// Check that the file argument supercedes the file in $page1
		self::assertSame('file2', $page2->file);

		// Make sure they are not references to the same underlying object
		self::assertNotSame($page1, $page2);
		// Make sure passing $page to create didn't modify the original
		self::assertSame($data, $page1->getArrayCopy());
		self::assertSame('file1', $page1->file);
	}

	public function test_copy(): void {
		// Create an arbitrary Page
		$page = Page::create('file');
		// The copy should be equal, but not the same
		$copy = Page::copy($page);
		self::assertNotSame($page, $copy);
		self::assertEquals($page, $copy);
	}

	public function test_href(): void {
		// Create an arbitrary Page
		$page = Page::create('file');

		// Pre-initialize the Smr\Session, since it uses 'rand', and we don't
		// want it to interfere with our rand seed when we call `href`, which
		// internally requires an Smr\Session.
		Session::getInstance();

		// The Page should not be modified when href() is called
		$expected = $page->getArrayCopy();
		srand(0); // for a deterministic SN
		$href = $page->href();
		self::assertSame(LOADER_URI . '?sn=qpbqzr', $href);
		self::assertSame($expected, $page->getArrayCopy());
	}

	public function test_addVar(): void {
		$page = Page::create('file');

		// Mock the current global $var
		$this->setVar(['index1' => 'value1', 'index2' => 'value2']);

		// Using the default $dest in addVar should reuse $source
		$page->addVar('index1');
		self::assertSame('value1', $page['index1']);

		// Specifying $dest should change the index in $page
		$page->addVar('index2', 'index3');
		self::assertSame('value2', $page['index3']);
		self::assertFalse(isset($page['index2']));
	}

	public function test_addVar_missing_source_raises(): void {
		// Create an arbitrary Page
		$page = Page::create('file');

		// Mock an empty global $var
		$this->setVar([]);

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Could not find "does_not_exist" in var!');
		$page->addVar('does_not_exist');
	}

	public function test_skipRedirect(): void {
		// Create an arbitrary Page with skipRedirect turned on
		$page = Page::create('file', skipRedirect: true);
		// Then skipRedirect should be on
		self::assertTrue($page->skipRedirect);

		// Create a new Page inheriting from the Page with skipRedirect
		$page2 = Page::create('file', $page);
		// Then skipRedirect should NOT be inherited (false by default)
		self::assertFalse($page2->skipRedirect);
	}

}
