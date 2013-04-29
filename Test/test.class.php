<?php
/*
class AbcdAction {
	public function test() {
		echo "this class is AbcdAction";
	}
}
var_dump ( class_exists ( 'abcdaction' ) );
*/

/*include 'RestFulClient.class.php';
$restc = new RestFulClient ();
$url = 'http://ruyi/AtomPHP/';
$data = array ('g' => 'Group', 'm' => 'Action', 'a' => 'function' );
print_r ( $restc->post ( $url, $data ) );*/

class A{
	function __construct(){
		echo "this is A class init";
	}
}

class B extends A{
	function __construct(){
		echo "this is B class init";
	}
}

$b = new B();
?>