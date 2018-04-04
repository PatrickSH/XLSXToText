<?php
include 'simplexlsx.class.php';
class ReadFile
{
    const INCLUDEHEADERROWS = false;

    const INCLUDEEMPTYVALUES = false;

    const WRITEMETHOD = "array"; //Can be: file(Requires destination file value),print(Will print array on screen), array(Will print as is) all is provided as string values

    const DESTINATIONFILE = "redirects.txt";

    const WRITETYPE = "redirectMatch 301 ^{0}/?$ {1}"; //can be text with variables corresponding to array numeric keys or implode

    const LIMITNUMBEROFROWS = 3; //This can either be false for all rows or max number of rows.

    public function __construct()
    {
        $this->file = 'sheet.xlsx';
        if( $this->data = SimpleXLSX::parse($this->file) ){
            $this->data = $this->data;
        } else {
            $this->data = "Error could not find file.";
        }
    }
    private function writeToFile($txt)
    {
        file_put_contents($this::DESTINATIONFILE, $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
    }
    public function getRows()
    {
        $rows = $this->data->rows();
        if($this::LIMITNUMBEROFROWS)
            $rows = array_slice($rows,0,$this::LIMITNUMBEROFROWS);

        return $rows;
    }
    public function getPreparedData()
    {
        $rows = $this->getRows();
        if(!$this::INCLUDEHEADERROWS){ //We dont wish to include header rows
            unset($rows[0]);
        }
        if($this::WRITEMETHOD == "file"){
            $startTime = microtime();
            foreach($rows as $rk => $row)
            {
                if(!$this::INCLUDEEMPTYVALUES){ //We dont want to include empty values so we have to run over values and see if they are empty
                    foreach($row as $k => $emptycheck)
                    {
                        if(is_null($emptycheck) || $emptycheck == "")
                        {
                            unset($rows[$rk][$k]);
                        }
                    }
                }
                if($this::WRITETYPE != "implode"){
                    preg_match_all('/{(.*?)}/', $this::WRITETYPE, $matches);
                    $keys = array_map('intval',$matches[1]);
                    $variables = [];
                    $values = [];
                    foreach($keys as $key)
                    {
                        $variables[] = "{". $key ."}";
                        if(!isset($row[$key]))
                            throw new Exception("Could not find row with key: ".$key." Check that variables defined in your writetype is reflected in XLSX document.");
                        $values[] = $row[$key];
                    }
                    $this->writeToFile(str_replace($variables,$values,$this::WRITETYPE));
                } else {
                    $this->writeToFile(implode(",",$row));
                }
            }
            $endTime = microtime();
            return "Success! started at: ".$startTime." Ended at: ".$endTime;
        }
        elseif($this::WRITEMETHOD == "array"){
            return $rows;
        }
        elseif($this::WRITEMETHOD == "print")
        {
            return print_r($rows);
        }
    }
}
$p = new ReadFile;
foreach($p->getPreparedData() as $row){
    var_dump($row);die;
    $entry = [
        'phone_number' => $row[4],
    ];
}