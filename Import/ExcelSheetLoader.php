<?php

namespace Hboie\DataIOBundle\Import;

use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;

class ExcelSheetLoader
{
    /**
     * @var Worksheet
     */
    private $phpWorksheet;

    /**
     * @var RowIterator
     */
    private $rowIterator;

    /**
     * @var array
     */
    private $colNames;

    /**
     * ExcelSheetLoader constructor.
     * @param $phpWorksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function __construct($phpWorksheet)
    {
        $this->phpWorksheet = $phpWorksheet;

        $this->colNames = array();

        $this->rowIterator = $this->phpWorksheet->getRowIterator();
        $row = $this->rowIterator->current();

        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach($cellIterator as $cell) {
            /** @var Cell $cell */
            $cInd = $cell->getColumn();
            if(!is_null($cell)) {
                $cellCont = $cell->getValue();
                if($cellCont != "") {
                    $this->colNames[$cInd] = strtolower($cellCont);
                }
            }
        }

        $this->next();
    }

    /**
     * @return int
     */
    public function getHighestRow()
    {
        return $this->phpWorksheet->getHighestRow();
    }

    /**
     * @return string
     */
    public function getHighestColumn()
    {
        return $this->phpWorksheet->getHighestColumn();
    }

    /**
     * @return array
     */
    public function getColNames()
    {
        return $this->colNames;
    }

    /**
     * @param string $col_name
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

    public function next()
    {
        $this->rowIterator->next();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->rowIterator->valid();
    }

    /**
     * @return int
     */
    public function getCurrentRowIndex()
    {
        return $this->rowIterator->current()->getRowIndex();
    }

    /**
     * @return array
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getRow()
    {
        $ret = array();

        foreach($this->colNames as $cInd => $cName) {

            $celValue = $this->phpWorksheet->getCell($cInd.$this->getCurrentRowIndex())->getFormattedValue();
            $ret[$cName] = $celValue;
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->phpWorksheet->getTitle();
    }
}