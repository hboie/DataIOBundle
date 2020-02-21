<?php

namespace Hboie\DataIOBundle\Export;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\ColumnIterator;
use PhpOffice\PhpSpreadsheet\Worksheet\RowIterator;

class ExcelSheetWriter
{
    /**
     * @var Worksheet
     */
    private $phpWorksheet;

    /**
     * @var ColumnIterator
     */
    private $colIterator;

    /**
     * @var RowIterator
     */
    private $rowIterator;

    /**
     * @var array
     */
    private $colIndices;

    /**
     * ExcelSheetWriter constructor.
     * @param $phpWorksheet
     */
    public function __construct($phpWorksheet)
    {
        $this->phpWorksheet = $phpWorksheet;

        $this->colIterator = $this->phpWorksheet->getColumnIterator();
        $this->rowIterator = $this->phpWorksheet->getRowIterator();
    }

    /**
     * @param string $colName
     */
    public function addColumn($colName)
    {
        $this->colIndices[$colName] = $this->colIterator->key();
        $this->colIterator->next();

        $this->phpWorksheet->setCellValue($this->colIndices[$colName] . $this->rowIterator->key(), $colName);
    }
    
    public function setRow($values)
    {
        $this->rowIterator->next();

        foreach ($values as $colName => $value) {
            if (isset($this->colIndices[$colName])) {
                $colKey = $this->colIndices[$colName];
                $this->phpWorksheet->setCellValue($colKey . $this->rowIterator->key(), $value);
            }
        }
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function closeSheet()
    {
        foreach ($this->colIndices as $ind) {
            $this->phpWorksheet->getColumnDimension($ind)->setAutoSize(true);
            $this->phpWorksheet->getStyle($ind . '1')->getFont()->setBold(true);
        }
    }
}