<?php

namespace A2Dborm;

class A2ResultRow {

	public function __construct(){
	}

	function __call( $method, $args ){
		return call_user_func_array(array(&$this->obj, $method), $args );
	}
	
}

?>