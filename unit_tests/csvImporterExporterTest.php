<?php

require_once('csvImporterExporter.php');

class csvImporterExporterTest extends PHPUnit_Framework_TestCase {  

  /**
   * Internal variable to hold sets of data parsed from test CSVs.
   */
  protected $_testDataSets = array();
  
  /**
   * Loops through set of test CSVs to get data sets.
   */
  public function setUp() { 
    $dataSetFiles = array('dataSetOne' => 'testing_csv_1.csv',
			  'dataSetTwo' => 'testing_csv_2.csv');
    
    foreach ($dataSetFiles as $dataSetName => $dataSetFile) {
      $foo = new csvImporterExporter();
      
      // Make sure loader didn't return false for invalid file
      $this->assertNotEquals(false, 
			     $foo->load('unit_tests/data_providers/' . $dataSetFile),
			     'Failed to load '.$dataSetFile.' for testing.');        
      
      // Add results to a test data set
      $this->_testDataSets[$dataSetName] = $foo->getData();
      
      unset($foo);
    }
  }
  
  /**
   * @test
   * @dataProvider arrayCombineData
   * @param array $keys
   * @param array $values 
   */
  public function testSafeArrayCombine($keys, $values) {
    $combinedArray = array();
    
    for ($i=0, $keyCount = count($keys); $i < $keyCount; $i++) {
      $combinedArray[$keys[$i]] = $values[$i];
    }
    
    // We expect the count of keys to match the overall array count
    // Truncating any extra data.
    $this->assertSame(count($keys), count($combinedArray));
  }
  
  /**
   * Provides sets of keys and values to test SafeArrayCombine().
   * @return array
   */
  public function arrayCombineData() {
    $testKeySets = array(
			 'testKeySetOne' => array('there',
						  'are',
						  'three headings'),
			 'testKeySetTwo' => array('here',
						  'are',
						  'four',
						  'headings'));
    
    $testValueSets = array(
			   'testValueSetOne' => array('but there',
						      'are',
						      'four',
						      'values'),
			   'testValueSetTwo' => array('and',
						      'here',
						      'there',
						      'are',
						      'five'));
    
    return array(
		 array($testKeySets['testKeySetOne'],
		       $testValueSets['testValueSetOne']),
		 array($testKeySets['testKeySetTwo'],
		       $testValueSets['testValueSetTwo']));
  }
  
  /**
   * Essentially just ensures that the data and headers were trimmed of every
   * test CSV.
   * @test
   */
  public function testGeneralData() {
    // Loop through all sets of test data 
    $i=1;
    foreach ($this->_testDataSets as $dataSet) {
      // Setting to true for testing purposes, needs to be configured
      // to work with the class
      $trimIsSet = true;
      
      // Make sure the processed data is equal to the trimmed version of itself
      if ($trimIsSet) {            
	// Make sure headers were trimmed
	$this->assertSame($this->arrayKeysRecursive($dataSet),
			  $this->arrayMapRecursive('trim', $this->arrayKeysRecursive($dataSet)),
			  'Headers weren\'t trimmed.');
	
	// Make sure data is identical to the trimmed version of itself
	$this->assertSame($dataSet, 
			  $this->arrayMapRecursive('trim', $dataSet), 
			  'Data wasn\'t trimmed.');
      }    
    }
  }
  
  /**
   * Not sure if this is of extreme use, it just verifies that the data 
   * returned is equal to what I made in the CSV, I just wanted to do an example
   * of data-specific tests.
   * 
   * @test
   * @depends testGeneralData
   */
  public function testOneData() {
    $actualTestOneData   = $this->_testDataSets['dataSetOne'];
    
    $expectedTestOneData = array(
				 array('heading 1' => 'foo',
				       'heading 2' => 'bar',
				       'heading 3' => 'baz'),
				 array('heading 1' => 'qux',
				       'heading 2' => 'fu',
				       'heading 3' => 'bar'));
    
    
    $this->assertEquals($expectedTestOneData, $actualTestOneData, 
			'Data didn\'t match in data-specific test in ' . __METHOD__);       
  }
  
  /**
   * Test two data is specifically useful because it does a good job of 
   * testing the safe array combine truncating extra columns of data in
   * particular rows.
   * @test
   * @depends testGeneralData
   */
  public function testTwoData() {
    $actualTestTwoData   = $this->_testDataSets['dataSetTwo'];
    
    $expectedTestTwoData = array(
				 array('heading 1' => 'foo',
				       'heading 2' => 'bar',
				       'heading 3' => 'baz'),
				 array('heading 1' => 'qux',
				       'heading 2' => 'fu',
				       'heading 3' => 'bar'),
				 array('heading 1' => 'a',
				       'heading 2' => 'b',
				       'heading 3' => 'c'));
    
    
    $this->assertEquals($expectedTestTwoData, $actualTestTwoData, 
			'Data didn\'t match in data-specific test in ' . __METHOD__);       
  }
  
  /**
   * Recursive array map, 'nuff said.
   * @param string $callback Function to map on each array value.
   * @param array $array 
   * @return array 
   */
  public function arrayMapRecursive($callback, $array) { 
    $results = array(); 
    $args    = array(); 
    
    if (func_num_args() > 2) { 
      $args = (array) array_shift(array_slice(func_get_args(), 2)); 
    }
    
    foreach ($array as $key => $value) { 
      $temp = $args; 
      array_unshift($temp,$value); 
      
      if (is_array($value)) { 
	array_unshift($temp,$callback); 
	
	$results[$key] = call_user_func_array(array('self', 'arrayMapRecursive'), $temp); 
      } else { 
	$results[$key] = call_user_func_array($callback, $temp); 
      } 
    } 
    
    return $results; 
  }
  
  /**
   * Recursively gets array keys...
   * @param array $array Array containing arrays of which you want keys of.
   * @param bool $unique Whether or not you want unique keys, or all instances of keys.
   * @return array One dimensional array of keys found in initial array.
   */
  public function arrayKeysRecursive($array, $unique=true) {     
    $arrayIterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
    
    $arrayKeys = array();
    
    foreach ($arrayIterator as $key => $value) {
      $arrayKeys[] = $key;
    }
    
    return ($unique) ? array_unique($arrayKeys) : $arrayKeys;
  }
}