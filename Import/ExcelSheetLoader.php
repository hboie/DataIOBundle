<?php

namespace Hboie\DataIOBundle\Import;

use Liuggio\ExcelBundle\LiuggioExcelBundle;

class ExcelSheetLoader
{
    /**
     * @var \PHPExcel_Worksheet
     */
    private $phpExcelSheet;

    /**
     * @var \PHPExcel_Worksheet_ColumnIterator
     */
    private $colIterator;

    /**
     * @var \PHPExcel_Worksheet_RowIterator
     */
    private $rowIterator;

    /**
     * @var array
     */
    private $colNames;

    /**
     * ExcelFileLoader constructor.
     * @param \PHPExcel_Worksheet $phpExcelSheet
     */
    public function __construct($phpExcelSheet)
    {
        $this->phpExcelSheet = $phpExcelSheet;

        $this->colNames = array();

        $this->rowIterator = $this->phpExcelSheet->getRowIterator();
        $row = $this->rowIterator->current();

        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        foreach($cellIterator as $cell) {
            /** @var \PHPExcel_Cell $cell */
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
        return $this->phpExcelSheet->getHighestRow();
    }

    /**
     * @return string
     */
    public function getHighestColumn()
    {
        return $this->phpExcelSheet->getHighestColumn();
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
     * @throws \PHPExcel_Exception
     */
    public function getRow()
    {
        $ret = array();

        foreach($this->colNames as $cInd => $cName) {

            $celValue = $this->phpExcelSheet->getCell($cInd.$this->getCurrentRowIndex())->getFormattedValue();
            $ret[$cName] = $celValue;
        }

        return $ret;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->phpExcelSheet->getTitle();
    }
}