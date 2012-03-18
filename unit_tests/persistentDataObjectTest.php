<?php

class persistentDataObjectTest extends PHPUnit_Framework_TestCase {  
  
  /**
   * @test
   */
  public function import() {
    // Just testing phpunit...
    $this->assertInstanceOf('persistentDataObjectTest', $this);
    $this->assertNotInstanceOf('stdClass', $this);        
  }
}