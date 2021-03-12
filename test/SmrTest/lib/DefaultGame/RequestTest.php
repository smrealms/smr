<?php declare(strict_types=1);

namespace SmrTest\lib\DefaultGame;

use Request;

/**
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
		$this->assertSame(2, Request::getInt('int', 3));
		// An index that exists, no default
		$this->assertSame(2, Request::getInt('int'));
		// An index that doesn't exist, with default
		$this->assertSame(3, Request::getInt('noexist', 3));
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
		$this->assertSame(3.14, Request::getFloat('float', 2.0));
		// An index that exists, no default
		$this->assertSame(3.14, Request::getFloat('float'));
		// An index that doesn't exist, with default
		$this->assertSame(3.14, Request::getFloat('noexist', 3.14));
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
		$this->assertSame(['a', 'b', 'c'], Request::getArray('array_str', []));
		// An index that exists, no default
		$this->assertSame(['a', 'b', 'c'], Request::getArray('array_str'));
		// An index that doesn't exist, with default
		$this->assertSame(['a'], Request::getArray('noexist', ['a']));
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
		$this->assertSame([1, 2, 3], Request::getIntArray('array_int', []));
		// An index that exists, no default
		$this->assertSame([1, 2, 3], Request::getIntArray('array_int'));
		// An index that doesn't exist, with default
		$this->assertSame([1], Request::getIntArray('noexist', [1]));
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
		$this->assertSame('ing', Request::get('str', 'foo'));
		// An index that exists, no default
		$this->assertSame('ing', Request::get('str'));
		// An index that doesn't exist, with default
		$this->assertSame('foo', Request::get('noexist', 'foo'));
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
		$this->assertSame('ing', Request::getVar('var:str'));
		// An index that exists in var but not request, with default
		$this->assertSame('ing', Request::getVar('var:str', 'foo'));
	}

	public function test_getVar_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame('ing', Request::getVar('str'));
		// An index that exists in request but not var, with default
		$this->assertSame('ing', Request::getVar('str', 'foo'));
		// An index neither in request nor var, with default
		$this->assertSame('foo', Request::getVar('noexist', 'foo'));
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
		// An index that exists in both var and request, with different values
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "str" inconsistent between $var and $_REQUEST!');
		Request::getVar('str');
	}

	public function test_getVar_index_same_in_both() {
		global $var;
		$var['str'] = 'ing';
		// An index that exists in both var and request, with the same value
		$this->assertSame('ing', Request::getVar('str'));
	}

	//------------------------------------------------------------------------

	public function test_getVarInt() {
		global $var;
		$var['var:int'] = 2;
		// An index that exists in var but not request, no default
		$this->assertSame(2, Request::getVarInt('var:int'));
		// An index that exists in var but not request, with default
		$this->assertSame(2, Request::getVarInt('var:int', 3));
	}

	public function test_getVarInt_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame(2, Request::getVarInt('int'));
		// An index that exists in request but not var, with default
		$this->assertSame(2, Request::getVarInt('int', 3));
		// An index neither in request nor var, with default
		$this->assertSame(3, Request::getVarInt('noexist', 3));
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
		// An index that exists in both var and request, with different values
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "int" inconsistent between $var and $_REQUEST!');
		Request::getVarInt('int');
	}

	public function test_getVarInt_index_same_in_both() {
		global $var;
		$var['int'] = 2;
		// An index that exists in both var and request, with the same value
		$this->assertSame(2, Request::getVarInt('int'));
	}

	//------------------------------------------------------------------------

	public function test_getVarIntArray() {
		global $var;
		$var['var:array_int'] = [1, 2, 3];
		// An index that exists in var but not request, no default
		$this->assertSame([1, 2, 3], Request::getVarIntArray('var:array_int'));
		// An index that exists in var but not request, with default
		$this->assertSame([1, 2, 3], Request::getVarIntArray('var:array_int', []));
	}

	public function test_getVarIntArray_no_var() {
		// An index that exists in request but not var, no default
		$this->assertSame([1, 2, 3], Request::getVarIntArray('array_int'));
		// An index that exists in request but not var, with default
		$this->assertSame([1, 2, 3], Request::getVarIntArray('array_int', []));
		// An index neither in request nor var, with default
		$this->assertSame([1], Request::getVarIntArray('noexist', [1]));
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
		// An index that exists in both var and request, with different values
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Index "array_int" inconsistent between $var and $_REQUEST!');
		Request::getVarIntArray('array_int');
	}

	public function test_getVarIntArray_index_same_in_both() {
		global $var;
		$var['array_int'] = [1, 2, 3];
		// An index that exists in both var and request, with the same value
		$this->assertSame([1, 2, 3], Request::getVarIntArray('array_int'));
	}

}
