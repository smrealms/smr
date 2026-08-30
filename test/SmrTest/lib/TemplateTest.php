<?php declare(strict_types=1);

namespace SmrTest\lib;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Smr\Combat\Results\Damage\ForceTakenDamage;
use Smr\Combat\Results\Damage\NormalTakenDamage;
use Smr\Container\DiContainer;
use Smr\Template;
use SmrTest\TestUtils;

#[CoversClass(Template::class)]
class TemplateTest extends TestCase {

	protected function setUp(): void {
		// Start each test with a fresh container (and Template instance).
		// This ensures the independence of each test.
		DiContainer::initialize(false);
	}

	public function test_pageTopic(): void {
		$template = Template::getInstance();
		$template->pageTopic = 'foo';
		self::assertSame($template->pageTopic, 'foo');
	}

	public function test_pageTopic_set_twice_throws(): void {
		$template = Template::getInstance();
		$template->pageTopic = 'foo';
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cannot re-assign pageTopic: foo');
		$template->pageTopic = 'bar';
	}

	public function test_doAn(): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'doAn');

		// Test vowel and consonant first letters, lowercase and uppercase
		self::assertSame('a', $method->invoke($template, 'Car'));
		self::assertSame('a', $method->invoke($template, 'house'));
		self::assertSame('an', $method->invoke($template, 'Egg'));
		self::assertSame('an', $method->invoke($template, 'apple'));
	}

	#[TestWith([3, 1, 1, '<span class="red">1</span> mine, <span class="red">1</span> combat drone and <span class="red">1</span> scout drone'])]
	#[TestWith([4, 0, 2, '<span class="red">2</span> mines and <span class="red">2</span> scout drones'])]
	#[TestWith([0, 2, 0, '<span class="red">2</span> combat drones'])]
	public function test_displayForceTakenDamage(int $mines, int $cds, int $sds, string $expected): void {
		$template = Template::getInstance();
		$damageTaken = new ForceTakenDamage(
			killingShot: false,
			targetAlreadyDead: false,
			minesDamage: 0,
			numMines: $mines,
			hasMines: false,
			combatDroneDamage: 0,
			numCombatDrones: $cds,
			hasCombatDrones: false,
			scoutDroneDamage: 0,
			numScoutDrones: $sds,
			hasScoutDrones: false,
			totalDamage: 0,
		);
		$result = $template->displayForceTakenDamage($damageTaken, kamikaze: 2);
		self::assertSame($expected, $result);
	}

	#[TestWith([1, 1, 1, '<span class="shields">1</span> shield, <span class="cds">1</span> combat drone and <span class="red">1</span> plate of armour'])]
	#[TestWith([2, 0, 2, '<span class="shields">2</span> shields and <span class="red">2</span> plates of armour'])]
	#[TestWith([0, 2, 0, '<span class="cds">2</span> combat drones'])]
	public function test_displayTakenDamage(int $shields, int $cds, int $armour, string $expected): void {
		$template = Template::getInstance();
		$damageTaken = new NormalTakenDamage(
			killingShot: false,
			targetAlreadyDead: false,
			shieldDamage: $shields,
			combatDroneDamage: 0,
			numCombatDrones: $cds,
			hasCombatDrones: false,
			armourDamage: $armour,
			totalDamage: 0,
		);
		$result = $template->displayTakenDamage($damageTaken);
		self::assertSame($expected, $result);
	}

	#[DataProvider('checkDisableAJAX_provider')]
	public function test_checkDisableAJAX(string $html, bool $expected): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'checkDisableAJAX');
		self::assertSame($expected, $method->invoke($template, $html));
	}

	/**
	 * @return array<array{string, bool}>
	 */
	public static function checkDisableAJAX_provider(): array {
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

	#[DataProvider('convertHtmlToAjaxXml_provider')]
	public function test_convertHtmlToAjaxXml(string $html, string $expected, bool $wrap = true): void {
		$template = Template::getInstance();
		$method = TestUtils::getPrivateMethod($template, 'convertHtmlToAjaxXml');
		// Wrap the HTML excerpt in the full document tags, if specified
		if ($wrap) {
			$html = self::html($html);
		}
		self::assertSame($expected, $method->invoke($template, $html, true));
	}

	/**
	 * @return array<array{string, string}|array{string, string, bool}>
	 */
	public static function convertHtmlToAjaxXml_provider(): array {
		return [
			// Span with an id
			['<span id="foo">Test</span>', '<foo>Test</foo>'],
			// Non-span with the ajax class
			['<div id="bar" class="ajax">Hello</div>', '<bar>Hello</bar>'],
			// The ajax class must be matched exactly (no partial string match)
			['<div id="bar" class="notajax">Goodbye</div>', ''],
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
			// Empty body
			['', ''],
			// Empty string
			['', '', false],
			// Ajax-enabled elements both outside and inside middle panel
			['<span id="foo">Test</span><div id="middle_panel">Foo</div>', '<foo>Test</foo><middle_panel>Foo</middle_panel>'],
			// HTML void elements retain their HTML serialization
			['<span id="tod">2021-04-24<br />01:39:51</span>', '<tod>2021-04-24&lt;br&gt;01:39:51</tod>'],
			// HTML5 elements are accepted by the parser
			['<details><summary>Locations</summary><span id="foo">Test</span></details>', '<foo>Test</foo>'],
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
		$result = $method->invoke($template, self::html(''), true);
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

	/**
	 * Provide the full HTML document tokens required by HTMLDocument
	 */
	private static function html(string $body): string {
		return '<!DOCTYPE html><html><head></head><body>' . $body . '</body></html>';
	}

}
