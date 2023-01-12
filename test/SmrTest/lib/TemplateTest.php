<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\TestCase;
use Smr\Container\DiContainer;
use Smr\Template;
use SmrTest\TestUtils;

/**
 * @covers Smr\Template
 */
class TemplateTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and Template instance).
		// This ensures the independence of each test.
		DiContainer::initialize(false);
	}

	public function test_assign_unassign(): void {
		$template = Template::getInstance();
		$template->assign('foo', 'bar');
		$this->assertTrue($template->hasTemplateVar('foo'));
		$template->unassign('foo');
		$this->assertFalse($template->hasTemplateVar('foo'));
	}

	public function test_assign_same_variable_twice_throws(): void {
		$template = Template::getInstance();
		$template->assign('foo', 'bar');
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot re-assign template variable \'foo\'!');
		try {
			$template->assign('foo', 'barbar');
		} catch (\Exception $err) {
			$template->unassign('foo'); // avoid destructor warning
			throw $err;
		}
	}

	public function test_doAn(): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'doAn');

		// Test vowel and consonant first letters, lowercase and uppercase
		$this->assertSame('a', $method->invoke($template, 'Car'));
		$this->assertSame('a', $method->invoke($template, 'house'));
		$this->assertSame('an', $method->invoke($template, 'Egg'));
		$this->assertSame('an', $method->invoke($template, 'apple'));
	}

	/**
	 * @dataProvider checkDisableAJAX_provider
	 */
	public function test_checkDisableAJAX(string $html, bool $expected): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'checkDisableAJAX');
		$this->assertSame($expected, $method->invoke($template, $html));
	}

	/**
	 * @return array<array{string, bool}>
	 */
	public function checkDisableAJAX_provider(): array {
		return [
			// Special input types that do not disable ajax
			['<input type="submit">', false],
			['<input type="hidden">', false],
			['<input type="image">', false],
			// All other input types *do* disable ajax
			['<input type="checkbox">', true],
			['<input type="number">', true],
			// Random HTML not related to inputs does not disable ajax
			['bla', false],
		];
	}

	/**
	 * @dataProvider convertHtmlToAjaxXml_provider
	 */
	public function test_convertHtmlToAjaxXml(string $html, string $expected): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'convertHtmlToAjaxXml');
		$this->assertSame($expected, $method->invoke($template, $html, true));
	}

	/**
	 * @return array<array{string, string}>
	 */
	public function convertHtmlToAjaxXml_provider(): array {
		return [
			// Span with an id
			['<span id="foo">Test</span>', '<foo>Test</foo>'],
			// Non-span with the ajax class
			['<div id="bar" class="ajax">Hello</div>', '<bar>Hello</bar>'],
			// Non-span *without* the ajax class
			['<div id="bar">Goodbye</div>', ''],
			// Middle panel with content that doesn't disable ajax
			['<div id="middle_panel">Foo</div>', '<middle_panel>Foo</middle_panel>'],
			['<div id="middle_panel"><input type="submit"></div>', '<middle_panel>&lt;input type="submit"&gt;</middle_panel>'],
			// Middle panel with ajax disabled by a specific input type
			['<div id="middle_panel"><form id="foo"><input type="checkbox"></form></div>', ''],
			// Middle panel with ajax disabled by a span with an id
			['<div id="middle_panel"><span id="foo">Test</span></div>', '<foo>Test</foo>'],
			// Middle panel with ajax disabled by the ajax class
			['<div id="middle_panel"><div id="bar" class="ajax">Hello</div></div>', '<bar>Hello</bar>'],
			// Empty string
			['', ''],
			// Ajax-enabled elements both outside and inside middle panel
			['<span id="foo">Test</span><div id="middle_panel">Foo</div>', '<foo>Test</foo><middle_panel>Foo</middle_panel>'],
		];
	}

	public function test_addJavascriptForAjax(): void {
		$template = Template::getInstance();

		// Make sure the added JS data is properly json-encoded
		$data = ['a' => 1, 'b' => 2];
		$result = $template->addJavascriptForAjax('test', $data);
		self::assertSame('{"a":1,"b":2}', $result);

		// This adds a special hook into convertHtmlToAjaxXml
		$method = TestUtils::getPrivateMethod($template, 'convertHtmlToAjaxXml');
		$result = $method->invoke($template, '<body></body>', true);
		self::assertSame('<JS><test>{"a":1,"b":2}</test></JS>', $result);
	}

	public function test_addJavascriptForAjax_duplicate(): void {
		$template = Template::getInstance();
		// Call once successfully
		$template->addJavascriptForAjax('test', '');
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Trying to set javascript val twice: test');
		$template->addJavascriptForAjax('test', '');
	}

}
