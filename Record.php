<?php

class Record implements ArrayAccess, Iterator, Countable {

    protected $_data = array();
      
    public function __construct(){
        $_args = func_get_args(); 
	
	if( count($_args) && is_array($_args[0]) ){
	    $this->_data = $_args[0];
	}
    }

// ArrayAccess, Iterator, Coutable stuff

    public function offsetSet($offset,$value) {
        if ($offset == "") {
	    $this->_data[] = $value;
	}else {
	    $this->_data[$offset] = $value;
	}
    }

    public function offsetExists($offset) {
     return isset($this->_data[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->_data[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }

    public function rewind() {
        reset($this->_data);
    }

    public function current() {
        return current($this->_data);
    }

    public function key() {
        return key($this->_data);
    }

    public function next() {
        return next($this->_data);
    }

    public function valid() {
        return $this->current() !== false;
    }

    public function count() {
     return count($this->_data);
    }


}


?>