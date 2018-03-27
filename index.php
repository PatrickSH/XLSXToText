<?php

include 'simplexlsx.class.php';

class ReadFile
{
    const INCLUDEHEADERROWS = false;
    
    const INCLUDEEMPTYVALUES = false;
    
    const WRITETOFILE = true;

    const DESTINATIONFILE = "redirects.txt";

    const WRITETYPE = "redirectMatch 301 ^{0}/?$ {1}"; //can be text with variables corresponding to array numeric keys or implode

    public function __construct()
    {
        $this->file = '20171030-301_redirects.xlsx';

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
        return $this->data->rows();
    }

    public function getPreparedData()
    {
        $rows = $this->getRows();

        if(!$this::INCLUDEHEADERROWS){ //We dont wish to include header rows
            unset($rows[0]);
        }

        if($this::WRITETOFILE){
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
        else
        {
            return print_r($rows);
        }
    }
}


//$p = new ReadFile;
//$p->getPreparedData();
