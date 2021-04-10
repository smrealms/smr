<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Page;

/**
 * This is an integration test, but does not need to extend BaseIntegrationTest
 * since we are not (or should not be!) writing any data.
 * @covers Page
 */
class PageIntegrationTest extends \PHPUnit\Framework\TestCase {

	public function test_create() {
		// Test create with $extra as array
		$page = Page::create('file', 'body', ['extra' => 'data']);
		// Check that the expected keys of the ArrayObject are set
		$expected = ['extra' => 'data', 'url' => 'file', 'body' => 'body'];
		self::assertSame($expected, $page->getArrayCopy());

		// Test create with $extra as a Page object
		$page2 = Page::create('file2', extra: $page);
		// Check that the expected keys of the ArrayObject are set
		$expected2 = ['extra' => 'data', 'url' => 'file2', 'body' => ''];
		self::assertSame($expected2, $page2->getArrayCopy());

		// Make sure they are not references to the same underlying object
		self::assertNotSame($page, $page2);
		// Make sure passing $page to create didn't modify the original
		self::assertSame($expected, $page->getArrayCopy());

		// Test create when setting $remainingPageLoads
		$page3 = Page::create('file', remainingPageLoads: 2);
		self::assertSame(2, $page3['RemainingPageLoads']);
	}

	public function test_copy() {
		// Create an arbitrary Page
		$page = Page::create('file');
		// The copy should be equal, but not the same
		$copy = Page::copy($page);
		self::assertNotSame($page, $copy);
		self::assertEquals($page, $copy);
	}

	public function test_useAsGlobalVar() {
		// Create an arbitrary Page
		$page = Page::create('file');

		// Unset the global $var in case it has been set elsewhere
		global $var;
		$var = null;

		// The global $var should be the same object as $page
		$page->useAsGlobalVar();
		self::assertSame($var, $page);
	}

	public function test_href() {
		// Create an arbitrary Page
		$page = Page::create('file');

		// Pre-initialize the Smr\Session, since it uses 'rand', and we don't
		// want it to interfere with our rand seed when we call `href`, which
		// internally requires an Smr\Session.
		\Smr\Session::getInstance();

		// The Page should not be modified when href() is called
		$expected = $page->getArrayCopy();
		srand(0); // for a deterministic SN
		$_SERVER['REQUEST_URI'] = 'loader.php'; // prevent "Undefined array key"
		$href = $page->href();
		self::assertSame('?sn=qpbqzr', $href);
		self::assertSame($expected, $page->getArrayCopy());
	}

	public function test_addVar() {
		$page = Page::create('file');

		// Unset the global $var in case it has been set elsewhere
		global $var;
		$var = Page::create('other');
		$var['index1'] = 'value1';
		$var['index2'] = 'value2';

		// Using the default $dest in addVar should reuse $source
		$page->addVar('index1');
		self::assertSame('value1', $page['index1']);

		// Specifying $dest should change the index in $page
		$page->addVar('index2', 'index3');
		self::assertSame('value2', $page['index3']);
		self::assertFalse(isset($page['index2']));
	}

	public function test_addVar_missing_source_raises() {
		// Create an arbitrary Page
		$page = Page::create('file');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Could not find "does_not_exist" in var!');
		$page->addVar('does_not_exist');
	}


}
