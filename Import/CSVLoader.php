<?php

namespace Hboie\DataIOBundle\Import;

class CSVLoader
{
    /**
     * @var resource
     */
    private $fileHandle;

    /**
     * @var integer
     */
    private $rowNb;

    /**
     * @var array
     */
    private $colNames;

    /**
     * @var array
     */
    private $rows;

    /**
     * @var array
     */
    private $currentRow;

    /**
     * @var bool
     */
    private $rowValid;

    public function __construct()
    {
        $this->rowValid = false;
    }

    /**
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     */

    public function openFile($filename, $delimiter = ';', $enclosure = '"', $escape = '"')
    {
        if (( $this->fileHandle = fopen($filename, "r")) !== FALSE) {

            $this->rowNb = 0;

            $this->colNames = array();

            $this->rows = array();
            $count = 0;
            while (($colValues = fgetcsv($this->fileHandle, null, $delimiter, $enclosure, $escape)) !== FALSE) {
                if ( $count == 0 ) {
                    foreach ( $colValues as $value ) {
                        array_push($this->colNames, strtolower($value));
                    }
                } else {
                    array_push($this->rows, $colValues);
                }

                $count++;
            }

            if ( $count > 1 ) {
                $this->rowValid = true;
                $this->getCurrentRow();
            }
        } else {
            $this->fileHandle = null;
        }
    }

    private function getCurrentRow()
    {
        $colValues = $this->rows[$this->rowNb];
        foreach($this->colNames as $cInd => $cName) {
            $celValue = $colValues[$cInd];
            $this->currentRow[$cName] = $celValue;
        }
    }

    /**
     * @return int
     */
    public function getHighestRow()
    {
        return count( $this->rows );
    }

    /**
     * @return string
     */
    public function getHighestColumn()
    {
        return count( $this->colNames );
    }

    /**
     * @return array
     */
    public function getColNames()
    {
        return $this->colNames;
    }

    /**
     * @param string $colName
     * @return bool
     */
    public function isValidColName($colName)
    {
        $ret = false;
        foreach($this->colNames as $name) {
            if($name == strtolower($colName)) {
                $ret = true;
            }
        }

        return $ret;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->rowValid;
    }

    /**
     * @return bool
     */
    public function next()
    {
        if ( $this->rowNb < count( $this->rows)-1 ) {
            $this->rowNb++;

            $this->getCurrentRow();

            return true;
        } else {
            $this->rowValid = false;

            return false;
        }
    }

    /**
     * @return int
     */
    public function getCurrentRowIndex()
    {
        return $this->rowNb;
    }

    /**
     * @return array
     */
    public function getRow()
    {
        return $this->currentRow;
    }

    /**
     * @return array
     */
    public function getCell($cName)
    {
        return $this->currentRow[strtolower($cName)];
    }


}