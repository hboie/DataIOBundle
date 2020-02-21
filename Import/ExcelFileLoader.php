<?php

namespace Hboie\DataIOBundle\Import;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Iterator;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Yectep\PhpSpreadsheetBundle\Factory;
use Hboie\DataIOBundle\Import\ExcelSheetLoader;

class ExcelFileLoader implements \Iterator
{
    /**
     * @var Factory
     */
    private $phpSpreadsheetFactory;

    /**
     * @var Spreadsheet
     */
    private $phpSpreadsheet;
    
    /**
     * @var Iterator
     */
    private $phpSpreadsheetWorksheetIterator;
    
    /**
     * ExcelFileLoader constructor.
     * @param Factory $phpSpreadsheetFactory
     */
    public function __construct($phpSpreadsheetFactory)
    {
        $this->phpSpreadsheetFactory = $phpSpreadsheetFactory;
        $this->currentSheetIndex = -1;
    }

    /**
     * @param $filename
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function openFile($filename)
    {
        $this->phpSpreadsheet = $this->phpSpreadsheetFactory->createSpreadsheet($filename);
        $this->phpSpreadsheetWorksheetIterator = $this->phpSpreadsheet->getWorksheetIterator();
    }

    /**
     * close file
     */
    public function closeFile()
    {
        $this->phpSpreadsheet->disconnectWorksheets();
    }

    /**
     * @return int
     */
    public function getNbOfSheets()
    {
        return $this->phpSpreadsheet->getSheetCount();
    }

    /**
     * @return Worksheet
     */
    public function getCurrentWorksheet()
    {
        return $this->phpSpreadsheetWorksheetIterator->current();
    }

    /**
     * @return \Hboie\DataIOBundle\Import\ExcelSheetLoader
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function getCurrentExcelSheetLoader()
    {
        $ret = new ExcelSheetLoader($this->phpSpreadsheetWorksheetIterator->current());
        return $ret;
    }
    
    public function next()
    {
        $this->phpSpreadsheetWorksheetIterator->next();
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->phpSpreadsheetWorksheetIterator->key();
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return $this->phpSpreadsheetWorksheetIterator->valid();
    }

    public function rewind()
    {
        $this->phpSpreadsheetWorksheetIterator->rewind();
    }

    /**
     * @return Worksheet
     */
    public function current()
    {
        return $this->phpSpreadsheetWorksheetIterator->current();
    }
}