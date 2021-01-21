<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Request;

/**
 * Class RequestTest
 * @package SmrTest\lib\DefaultGame
 * @covers Request
 */
class RequestTest extends \PHPUnit\Framework\TestCase {

	public static function setUpBeforeClass() : void {
		$_REQUEST = [
			'int' => 2,
			'float' => 3.14,
			'str' => 'ing',
			'array_empty' => [],
			'array_str' => ['a', 'b', 'c'],
			'array_int' => [1, 2, 3],
		];
	}

	protected function setUp() : void {
		// Reset $var for each test function
		unset($GLOBALS['var']);
	}

	//------------------------------------------------------------------------

	public function test_has() {
		// An index that exists
		$this->assertTrue(Request::has('str'));
		// An index that doesn't exist
		$this->assertFalse(Request::has('noexist'));
	}

	//------------------------------------------------------------------------

	public function test_getInt() {
		// An index that exists, with default
		$this->assertSame(Request::getInt('int', 3), 2);
		// An index that exists, no default
		$this->assertSame(Request::getInt('int'), 2);
		// An index that doesn't exist, with default
		$this->assertSame(Request::getInt('noexist', 3), 3);
	}

	public function test_getInt_exception() {
		// An index that doesn't exist, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getInt('noexist');
	}

	//------------------------------------------------------------------------

	public function test_getFloat() {
		// An index that exists, with default
		$this->assertSame(Request::getFloat('float', 2.0), 3.14);
		// An index that exists, no default
		$this->assertSame(Request::getFloat('float'), 3.14);
		// An index that doesn't exist, with default
		$this->assertSame(Request::getFloat('noexist', 3.14), 3.14);
	}

	public function test_getFloat_exception() {
		// An index that doesn't exist, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getFloat('noexist');
	}

	//------------------------------------------------------------------------

	public function test_getArray() {
		// An index that exists, with default
		$this->assertSame(Request::getArray('array_str', []), ['a', 'b', 'c']);
		// An index that exists, no default
		$this->assertSame(Request::getArray('array_str'), ['a', 'b', 'c']);
		// An index that doesn't exist, with default
		$this->assertSame(Request::getArray('noexist', ['a']), ['a']);
	}

	public function test_getArray_exception() {
		// An index that doesn't exist, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getArray('noexist');
	}

	//------------------------------------------------------------------------

	public function test_getIntArray() {
		// An index that exists, with default
		$this->assertSame(Request::getIntArray('array_int', []), [1, 2, 3]);
		// An index that exists, no default
		$this->assertSame(Request::getIntArray('array_int'), [1, 2, 3]);
		// An index that doesn't exist, with default
		$this->assertSame(Request::getIntArray('noexist', [1]), [1]);
	}

	public function test_getIntArray_exception() {
		// An index that doesn't exist, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getIntArray('noexist');
	}

	//------------------------------------------------------------------------

	public function test_get() {
		// An index that exists, with default
		$this->assertSame(Request::get('str', 'foo'), 'ing');
		// An index that exists, no default
		$this->assertSame(Request::get('str'), 'ing');
		// An index that doesn't exist, with default
		$this->assertSame(Request::get('noexist', 'foo'), 'foo');
	}

	public function test_get_exception() {
		// An index that doesn't exist, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::get('noexist');
	}

	//------------------------------------------------------------------------

	public function test_getVar() {
		global $var;
		$var['var:str'] = 'ing';
		// An index that exists in var but not request, no default
		$this->assertSame(Request::getVar('var:str'), 'ing');
		// An index that exists in var but not request, with default
		$this->assertSame(Request::getVar('var:str', 'foo'), 'ing');
	}

	public function test_getVar_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame(Request::getVar('str'), 'ing');
		// An index that exists in request but not var, with default
		$this->assertSame(Request::getVar('str', 'foo'), 'ing');
		// An index neither in request nor var, with default
		$this->assertSame(Request::getVar('noexist', 'foo'), 'foo');
	}

	public function test_getVar_exception_no_default() {
		// An index that doesn't exist in request or var, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getVar('noexist');
	}

	public function test_getVar_exception_index_in_both() {
		global $var;
		$var['str'] = 'ing:var';
		// An index that exists in both var and request
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "str" must not be in both $var and $_REQUEST!');
		Request::getVar('str');
	}

	//------------------------------------------------------------------------

	public function test_getVarInt() {
		global $var;
		$var['var:int'] = 2;
		// An index that exists in var but not request, no default
		$this->assertSame(Request::getVarInt('var:int'), 2);
		// An index that exists in var but not request, with default
		$this->assertSame(Request::getVarInt('var:int', 3), 2);
	}

	public function test_getVarInt_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame(Request::getVarInt('int'), 2);
		// An index that exists in request but not var, with default
		$this->assertSame(Request::getVarInt('int', 3), 2);
		// An index neither in request nor var, with default
		$this->assertSame(Request::getVarInt('noexist', 3), 3);
	}

	public function test_getVarInt_exception_no_default() {
		// An index that doesn't exist in request or var, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getVarInt('noexist');
	}

	public function test_getVarInt_exception_index_in_both() {
		global $var;
		$var['int'] = 3;
		// An index that exists in both var and request
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "int" must not be in both $var and $_REQUEST!');
		Request::getVarInt('int');
	}

	//------------------------------------------------------------------------

	public function test_getVarIntArray() {
		global $var;
		$var['var:array_int'] = [1, 2, 3];
		// An index that exists in var but not request, no default
		$this->assertSame(Request::getVarIntArray('var:array_int'), [1, 2, 3]);
		// An index that exists in var but not request, with default
		$this->assertSame(Request::getVarIntArray('var:array_int', []), [1, 2, 3]);
	}

	public function test_getVarIntArray_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame(Request::getVarIntArray('array_int'), [1, 2, 3]);
		// An index that exists in request but not var, with default
		$this->assertSame(Request::getVarIntArray('array_int', []), [1, 2, 3]);
		// An index neither in request nor var, with default
		$this->assertSame(Request::getVarIntArray('noexist', [1]), [1]);
	}

	public function test_getVarIntArray_exception_no_default() {
		// An index that doesn't exist in request or var, no default
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('No request variable "noexist"');
		Request::getVarIntArray('noexist');
	}

	public function test_getVarIntArray_exception_index_in_both() {
		global $var;
		$var['array_int'] = [];
		// An index that exists in both var and request
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "array_int" must not be in both $var and $_REQUEST!');
		Request::getVarIntArray('array_int');
	}

}
