<?php
echo "<pre>";
echo "<h1>this file is : " . __FILE__ . "</h1>\n";
echo "<hr>";
echo "<hr>";

echo "\n<h3>=========START=========</h2>\n";
$timer = array ();
$timer ['START'] = microtime ( TRUE );
echo "<hr>";

//系统目录定义
defined ( 'ATOM_PATH' ) or define ( 'ATOM_PATH', dirname ( __FILE__ ) . '/../' );
//应用程序目录
defined ( 'APP_PATH' ) or define ( 'APP_PATH', dirname ( $_SERVER ['SCRIPT_FILENAME'] ) . '/../' );
require '../AtomPHP.php';

echo "<h2>Test to set C() value</h2>\n";
echo "<hr>";

/**
 * 赋值测试
 */
//数组赋值测试
C ( array ('test_array' => 'test conf array have key' ) );
C ( array ('test conf array no key' ) );
C ( 'test_array_1', array ('test key to value set' ) );

//字符串类型赋值测试
C ( 'test_string_1', "test string" );
C ( 'test_string_2', 'test special string ~!@#$%^&*()_+' );
C ( 'test_string_3', "test special ' , . ? / | ; : " );
C ( 'test_string_4', 'test special , . ? / \ | ; : ' );

//其它类型赋值
class TestC {
	function testFunction() {
		return "class name is " . __CLASS__ . ", function name is " . __METHOD__;
	}
}
C ( 'test_Object', new TestC () );
function testC() {
	return "function name is " . __METHOD__;
}
C ( 'test_function', testC () );
C ( 1, 'this key is 1' );

//数值赋值测试
C ( 'test_number_1', 100 );
C ( 'test_number_2', 100 / 10 );
C ( 'test_number_3', 999999999999999 );

//点赋值
C ( 'a.test_dot_1', 'the key is a.test_dot_1 ' );
C ( 'a.test_dot_2', array ('the key is a.test_dot_2, the value is array' ) );
C ( 'A.test_dot_3', 'the key is A.test_dot_3, conf.php have key' );
C ( 'A.test_dot_4', array ('the key is A.test_dot_4, the value is array, conf.php have key' ) );
C ( 'conf.A.test_dot_5', "the key is conf.A.test_dot_5" );
C ( 'conf.A.test_dot_6', array ("conf.A.test_dot_6, the value is array" ) );
C ( 'other.A.test_dot_7', "the key is other.A.test_dot_7, other.php is not found" );

C ( 'a.b.test_dot_1', 'the key is a.b.test_dot_1 ' );
C ( 'a.b.test_dot_2', array ('the key is a.b.test_dot_2, the value is array' ) );
C ( 'A.b.test_dot_3', 'the key is A.b.test_dot_3, conf.php have key' );
C ( 'A.b.test_dot_4', array ('the key is A.b.test_dot_4, the value is array, conf.php have key' ) );
C ( 'conf.A.b.test_dot_5', "the key is conf.A.b.test_dot_5" );
C ( 'conf.A.b.test_dot_6', array ("conf.A.b.test_dot_6, the value is array" ) );
C ( 'other.A.b.test_dot_7', "the key is other.A.b.test_dot_7, other.php is not found" );

//全部输出
echo "<h1>all C() out print_r</h1>\n";
echo "<hr>";
print_r ( C () );
echo "<h1>all C() out var_dump</h1>\n";
echo "<hr>";
var_dump ( C () );

/**************************************************/

/**
 * 赋值输出测试
 */
echo "<h2>Test to get C() value</h2>\n";
echo "<h2>Notic : the key must be upper</h2>\n";
echo "<hr>";

//数组获取测试
echo "\n C ( 'test_array' ) ==> ";
var_dump ( C ( 'test_array' ) );

echo "\n C ( 0 ) ==> ";
var_dump ( C ( 0 ) );

echo "\n C ( 'TEST_ARRAY_1' ) ==> ";
var_dump ( C ( 'TEST_ARRAY_1' ) );

//字符串类型获取测试
echo "\n C ( 'TEST_STRING_1') ==> ";
var_dump ( C ( 'TEST_STRING_1' ) );

echo "\n C ( 'TEST_STRING_2') ==> ";
var_dump ( C ( 'TEST_STRING_2' ) );

echo "\n C ( 'TEST_STRING_3') ==> ";
var_dump ( C ( 'TEST_STRING_3' ) );

echo "\n C ( 'TEST_STRING_4') ==> ";
var_dump ( C ( 'TEST_STRING_4' ) );

//其它类型赋值
echo "\n C ( 'TEST_OBJECT') ==> ";
var_dump ( C ( 'TEST_OBJECT' ) );

echo "\n C ( 'TEST_FUNCTION') ==> ";
var_dump ( C ( 'TEST_FUNCTION' ) );

echo "\n C ( 1 ) ==> ";
var_dump ( C ( 1 ) );

//数值赋值测试
echo "\n C ( 'TEST_NUMBER_1') ==> ";
var_dump ( C ( 'TEST_NUMBER_1' ) );

echo "\n C ( 'TEST_NUMBER_2') ==> ";
var_dump ( C ( 'TEST_NUMBER_2' ) );

echo "\n C ( 'TEST_NUMBER_3') ==> ";
var_dump ( C ( 'TEST_NUMBER_3' ) );

//点赋值
echo "\n C ( 'a.TEST_DOT_1') ==> ";
var_dump ( C ( 'a.TEST_DOT_1' ) );

echo "\n C ( 'a.TEST_DOT_2') ==> ";
var_dump ( C ( 'a.TEST_DOT_2' ) );

echo "\n C ( 'A.TEST_DOT_3') ==> ";
var_dump ( C ( 'A.TEST_DOT_3' ) );

echo "\n C ( 'A.TEST_DOT_4') ==> ";
var_dump ( C ( 'A.TEST_DOT_4' ) );

echo "\n C ( 'conf.A.TEST_DOT_5') ==> ";
var_dump ( C ( 'conf.A.TEST_DOT_5' ) );

echo "\n C ( 'conf.A.TEST_DOT_6') ==> ";
var_dump ( C ( 'conf.A.TEST_DOT_6' ) );

echo "\n C ( 'other.A.TEST_DOT_7') ==> ";
var_dump ( C ( 'other.A.TEST_DOT_7' ) );

echo "\n C ( 'a.b.TEST_DOT_1') ==> ";
var_dump ( C ( 'a.b.TEST_DOT_1' ) );

echo "\n C ( 'a.b.TEST_DOT_2') ==> ";
var_dump ( C ( 'a.b.TEST_DOT_2' ) );

echo "\n C ( 'A.b.TEST_DOT_3') ==> ";
var_dump ( C ( 'A.b.TEST_DOT_3' ) );

echo "\n C ( 'A.b.TEST_DOT_4') ==> ";
var_dump ( C ( 'A.b.TEST_DOT_4' ) );

echo "\n C ( 'conf.A.b.TEST_DOT_5') ==> ";
var_dump ( C ( 'conf.A.b.TEST_DOT_5' ) );

echo "\n C ( 'conf.A.b.TEST_DOT_6') ==> ";
var_dump ( C ( 'conf.A.b.TEST_DOT_6' ) );

echo "\n C ( 'other.A.b.TEST_DOT_7') ==> ";
var_dump ( C ( 'other.A.b.TEST_DOT_7' ) );

echo "\n<h3>=========END=========</h2>\n";
$timer ['END'] = microtime ( TRUE );
$timer ['DIFF'] = $timer ['END'] - $timer ['START'];
print_r ( $timer );
echo "<hr>";
?>