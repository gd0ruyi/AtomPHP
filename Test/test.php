<?php
echo "<pre>";

date_default_timezone_set ( 'PRC' );
echo date ( 'Y-m-d H:i:s', 1355125781 ) . "\n";

date_default_timezone_set ( 'America/New_York' );
echo date ( 'Y-m-d H:i:s', 1355125781 ) . "\n";

$conf = require_once 'Test/test2.phpphp';
print_r ( $conf );

print_r ( explode ( '@', 'ttac' ) );
print_r ( array_change_key_case ( array ("a" => 'b' ) ) );
$file = $_SERVER ['SCRIPT_FILENAME'];
echo substr ( $file, - 3, 4 );
var_dump ( is_dir ( $file ) );
var_dump ( basename ( $file ) );
echo var_export ( array (1, 2, 3 ), true );
print_r ( array ('A' => 1, 'a' => 2 ) );
echo strstr('a@b@c', '@');
die ();

function strToAsc($string) {
	$length = strlen ( $string );
	if ($length <= 0) {
		die ( 'function strToAsc is error' );
	}
	$int = 0;
	for($i = 0; $i < $length; $i ++) {
		$int += ord ( $string {$i} );
	}
	return $int;
}

//$php_str = file_get_contents ( 'test1.php' );
$php_str = php_strip_whitespace ( 'test1.php' );
//print_r ( token_get_all ( $php_str ) );
$php_str = substr ( $php_str, 5 );
$php_str = substr ( $php_str, 0, strlen ( $php_str ) - 2 );
$php_str_len = strlen ( $php_str );
//$php_str_len = 1024;


echo "this is php code >> " . $php_str . "\n";

if (! function_exists ( 'shmop_open' )) {
	die ( 'prel function shmop_open is not open in php.ini' );
}

$id = 'Test';
//$id = __FILE__;
$id = strToAsc ( $id );
//$id = ftok ( $id, 't' );
echo $id;
var_dump ( $id );
//die();
//$id = md5($id);
//$id = strToAsc ( $id );


$shmop_id = shmop_open ( $id, "c", 0644, $php_str_len );
if (! $shmop_id) {
	echo "error: shomp is error >> id = {$shmop_id}";
}
$memory = shmop_read ( $id, 0, $php_str_len );
if ($memory) {
	echo "read memory >>" . $memory . "\n";
} else {
	echo "write memory >>";
	$memory = shmop_write ( $id, $php_str, 0 );
}

echo $memory;

//eval ( $php_str );
//$test = new Test ();
//$test->printTest ();
shmop_close ();
?>