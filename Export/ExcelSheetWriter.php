<?php

namespace Hboie\DataIOBundle\Export;

use Liuggio\ExcelBundle\LiuggioExcelBundle;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExcelSheetWriter
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
    private $colIndices;

    /**
     * ExcelFileLoader constructor.
     * @param \PHPExcel_Worksheet $phpExcelSheet
     */
    public function __construct($phpExcelSheet)
    {
        $this->phpExcelSheet = $phpExcelSheet;

        $this->colIterator = $this->phpExcelSheet->getColumnIterator();
        $this->rowIterator = $this->phpExcelSheet->getRowIterator();
    }

    /**
     * @param string $colName
     */
    public function addColumn($colName)
    {
        $this->colIndices[$colName] = $this->colIterator->key();
        $this->colIterator->next();

        $this->phpExcelSheet->setCellValue($this->colIndices[$colName] . $this->rowIterator->key(), $colName);
    }
    
    public function setRow($values)
    {
        $this->rowIterator->next();

        foreach ($values as $colName => $value) {
            if (isset($this->colIndices[$colName])) {
                $colKey = $this->colIndices[$colName];
                $this->phpExcelSheet->setCellValue($colKey . $this->rowIterator->key(), $value);
            }
        }
    }

    public function closeSheet()
    {
        foreach ($this->colIndices as $ind) {
            $this->phpExcelSheet->getColumnDimension($ind)->setAutoSize(true);
            $this->phpExcelSheet->getStyle($ind . '1')->getFont()->setBold(true);
        }
    }
}