<?php

require_once('persistentDataObject.php');

class csvImporterExporter extends persistentDataObject {
    /**
     * Specific CSV file to be manipulated.
     * @var string
     */
    protected $_filename;
    
    /**
     * Numbers of rows in the CSV, excludes header rows.
     * @var int
     */
    protected $_rows = 0;
    
    /**
     * Number of rows processed, if a row fails any validation,
     * it's not included here.
     * @var int
     */
    protected $_rows_processed = 0;

    /**
     * An array of column headers.
     * @var array
     */
    protected $_headers = array();
    
    /**
     * Runs trim() on all headers and CSV data before it's passed
     * to $this->_data or $this->_processData().
     * @var bool
     */
    protected $_trim_all = true;

    /**
     * The Delimiter used for the file.
     * @var string
     */
    protected $_delimeter = ',';


    /**
     *
     */
     protected $_enclosure = '';

    /**
     * Whether or not the first row is the column headers.
     * @var bool
     */
    protected $_with_headers = true;

    /**
     * Construct of the CSV importer/exporter, optionally a filename
     * can be passed in as the first argument to set it for the object.
     */
    public function __construct(){
        $_args = func_get_args();
         
        switch(func_num_args()) {
            case 1:
                $this->_filename = $_args[0];
            break;
        }
    }

    public function getHeaders(){
    	   return $this->_headers;
    }


    /**
     * Loads the filename, either declared in the construct,
     * or directly in this method. Fails if the file isn't readable or
     * doesn't exist, processes the CSV, and handles trimming.
     * @param string $filename
     * @return inst|bool csvImporterExporter on success, false on failure. 
     */
    public function load($className = '',$filename=null) {
        if ($this->_filename === null) {
            $this->_filename = $filename;    
        }
        
        // Returns false if file isn't readable/doesn't exist, or isn't a csv.
        // Should throw errors.
        if (!is_readable($this->_filename)) {
            return false;
        } elseif (substr($this->_filename, (strlen($this->_filename) - 4), 4) !== '.csv') { 
            return false;
        }
        
        $this->_processCSV($className);
        
        return $this;
    }
    
    public function getData() {
        return $this->_data;
    }

    public function save($filename=null) {
        if( $filename == null ){
	    $filename = $this->_filename;
	}
	
	if( $handle = fopen($filename, 'w') ){
	    if( $this->_with_headers ){
	        fputcsv( $handle, $this->getHeaders(),$this->_delimeter, $this->_enclosure );
	    }

	    foreach( $this->getData() AS $data){
	        fputcsv( $handle, $data, $this->_delimeter, $this->_enclosure );
	    }
        } else {
	    throw new Exception( "could not open " . $filename . " for writing!");
	}

        return true;
    }

    /**
     * Processes the CSV data.
     */
    private function _processCSV($className = '') {
        $handle = fopen($this->_filename, 'r');
                
        // If headers exist, get them first and set it to $this->_headers.
        if ($this->_with_headers && ($data = fgetcsv($handle, 0, $this->_delimeter))) {
            if ($this->_validateColumnNames($data)) {
                $data = $this->_processColumnNames($data);
                $this->_headers = ($this->_trim_all) ? array_map('trim', $data) : $data;
            }
        }

        // Do all non-header data processing
        while ($data = fgetcsv($handle, 0, $this->_delimeter)) {
            $this->_rows++;
            
            // New functionality, rows going outside of the amount of column
            // headers will be truncated.
            $data = $this->_safeArrayCombine($this->_headers, $data);            

            // Validate data, then make sure it gets into rows processed count
            if ($this->_validateData($data)) {
                if ($this->_trim_all) { 
                    $data = array_map('trim', $data);
                }
                if( (bool) $className && class_exists($className)){
		    $this->_data[] = new $className( $this->_processData($data) );
		    $this->_rows_processed++;
		} else {
                    $this->_data[] = $this->_processData($data);
                    $this->_rows_processed++;
		}
            }
        }

        fclose($handle);
        
        return $this;
    }

    protected function _validateColumnNames($names) {
        return true;    
    }

    protected function _validateData($data) {
        return true;
    }
    
    protected function _processColumnNames($columnNames) {
        return $columnNames;
    }

    /**
     * Processes data before it's added into the $_data array.
     * Business logic should override this function.
     * @param array $data
     * @return array Processed $data.
     */
    protected function _processData($data) {
        return $data;    
    }
    
    /**
     * Combines a set of keys and values to in one array, but an array element
     * will only be created as long as it has a key, any extra values will
     * be ignored.
     * @param type $keys
     * @param type $values
     * @return type 
     */
    private function _safeArrayCombine($keys, $values) {
        $combinedArray = array();
        
        for ($i=0, $keyCount = count($keys); $i < $keyCount; $i++) {
            $combinedArray[$keys[$i]] = $values[$i];
        }
        
        return $combinedArray;
    }
}