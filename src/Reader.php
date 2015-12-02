<?php namespace BLM;

/**
 * BLM File Reader
 * For use with Rightmoves BLM files 
 * Built for the Version 3 Specification
 * @throws Exception
 */
class Reader
{
    /**
     * @var string File content
     */
    protected $file;

    /**
     * @var array Headers section as array
     */
    protected $headers;

    /**
     * @var array Definitions / Array keys for data section rows
     */
    protected $definitions;

    /**
     * init
     * @throws Exception
     * @param $file string File path/location of the .BLM file
     */
    public function __construct($file)
    {
        // make sure a .BLM file has been given 
        $fileNameParts = explode('.',$file);
        $ext = end($fileNameParts);
        $ext = strtoupper($ext);
        if($file=='' || $ext!='BLM') {
            throw new Exception("Given file is not a .BLM");
        }

        // read file 
        $this->file = file_get_contents($file);
    }

    /**
     * Return BLM file as Array
     */
    public function toArray()
    {
        // Get headers, then definitions/keys, then get the data 
        $this->headers = $this->getHeaders();
        $this->definitions = $this->getDefinitions();
        return $this->getData();
    }

    /**
     * Get headers
     * @throws Exception
     * @return array
     */
    protected function getHeaders()
    {
        // Get header section, throw exception if not found
        if( ! preg_match('/#HEADER#(.*?)#/sm', $this->file, $match)) {
            throw new Exception("No #HEADER# provided");
        }
        $params = array();
        // get all lines from the header section
        $lines = explode("\n", $match[1]);
        foreach($lines as $line) {
            if(trim($line)!='') {
                // seperate out the key/val pair
                $parts = explode(' : ', $line);
                // replace single/double quotes at the start/end of the string
                $value = preg_replace('/(^[\'"]|[\'"]$)/', '', trim($parts[1]));
                $params[trim($parts[0])] = $value;
            }
        }
        return $params;
    }

    /**
     * Get definitions
     * @throws Exception
     * @return array
     */
    protected function getDefinitions()
    {
        if(empty($this->headers)) {
            throw new Exception("Please set headers first.");
        }
        // Get definitions section, throw exception if not found
        if( ! preg_match('/#DEFINITION#(.*?)\#/sm', $this->file, $match)) {
            throw new Exception("No #DEFINITION# provided");
        }

        // split line by the filed seperator (EndOfField)
        $fields = array_map('trim', explode($this->headers['EOF'], $match[1]));

        // remove line/row seperator (EndOfRow)
        $fieldsEnd = end($fields);
        if($fieldsEnd==$this->headers['EOR']) {
            $fieldKeys = array_keys($fields);
            unset( $fields[array_pop($fieldKeys)] );
        }
        return $fields;
    }

    /**
     * Get data
     * @throws Exception
     * @return array
     */
    protected function getData()
    {
        if(empty($this->definitions)) {
            throw new Exception("Please set definitions first.");
        }
        // Get data section, throw exception if not found
        if( ! preg_match('/#DATA#(.*?)#END#/sm', $this->file, $match)) {
            throw new Exception("No #DATA# provided (or no #END# defined)");
        }

        // setup an array for the final output 
        $data = array();

        // get all rows
        $rows = array_map('trim', explode($this->headers['EOR'], $match[1]));
        
        // loop rows 
        foreach($rows as $i => $row) {
            // get all fields in row 
            $fields = array_map('trim', explode($this->headers['EOF'], $row));
            // if this row is a real row
            if(count($fields)>1) {
                $data[$i] = array();
                // loop fields, and stick them into an array of fields inside an array of rows
                foreach($fields as $k => $field) {
                    if(isset($this->definitions[$k])) {
                        $data[$i][ $this->definitions[$k] ] = $field;
                    }
                }
            }
        }
        return $data;
    }
}
