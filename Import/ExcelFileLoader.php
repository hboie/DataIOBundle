<?php

namespace Hboie\DataIOBundle\Import;

use Liuggio\ExcelBundle\LiuggioExcelBundle;
use Hboie\DataIOBundle\Import\ExcelSheetLoader;

class ExcelFileLoader implements \Iterator
{
    /**
     * @var \Liuggio\ExcelBundle\Factory
     */
    private $phpExcelFactory;

    /**
     * @var \PHPExcel
     */
    private $phpExcelObj;
    
    /**
     * @var \PHPExcel_WorksheetIterator
     */
    private $phpExcelWorksheetIterator;
    
    /**
     * ExcelFileLoader constructor.
     * @param \Liuggio\ExcelBundle\Factory $phpExcelFactory
     */
    public function __construct($phpExcelFactory)
    {
        $this->phpExcelFactory = $phpExcelFactory;
        $this->currentSheetIndex = -1;
    }

    /**
     * open Excel file
     * @param string $filename
     */
    public function openFile($filename)
    {
        $this->phpExcelObj = $this->phpExcelFactory->createPHPExcelObject($filename);
        $this->phpExcelWorksheetIterator = $this->phpExcelObj->getWorksheetIterator();
    }

    /**
     * close file
     */
    public function closeFile()
    {
        $this->phpExcelObj->disconnectWorksheets();
    }

    /**
     * @return int
     */
    public function getNbOfSheets()
    {
        return $this->phpExcelObj->getSheetCount();
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function getCurrentWorksheet()
    {
        return $this->phpExcelWorksheetIterator->current();
    }

    /**
     * @return ExcelSheetLoader
     */
    public function getCurrentExcelSheetLoader()
    {
        $ret = new ExcelSheetLoader($this->phpExcelWorksheetIterator->current());
        return $ret;
    }
    
    public function next()
    {
        $this->phpExcelWorksheetIterator->next();
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->phpExcelWorksheetIterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->phpExcelWorksheetIterator->valid();
    }

    public function rewind()
    {
        $this->phpExcelWorksheetIterator->rewind();
    }

    /**
     * @return \PHPExcel_Worksheet
     */
    public function current()
    {
        return $this->phpExcelWorksheetIterator->current();
    }
}