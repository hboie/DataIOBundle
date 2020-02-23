<?php

namespace Hboie\DataIOBundle\Export;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Yectep\PhpSpreadsheetBundle\Factory;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelFileCreator
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
     * @var string $phpExcelType
     */
    private $phpExcelType;

    /**
     * @var array $sheets
     */
    private $sheets;

    /**
     * ExcelFileCreator constructor.
     * @param $phpSpreadsheetFactory
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function __construct($phpSpreadsheetFactory)
    {
        $this->phpSpreadsheetFactory = $phpSpreadsheetFactory;

        $this->phpSpreadsheet = $this->phpSpreadsheetFactory->createSpreadsheet();
        $this->phpSpreadsheet->getProperties()->setCreator("OneReporting");
        $this->phpExcelType = 'Xlsx';

        $this->sheets = array();
    }

    /**
     * @param $title
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function addSheet($title)
    {
        $nbOfSheets = count($this->sheets);

        if ($nbOfSheets > 0) {
            $this->phpSpreadsheet->createSheet($nbOfSheets);
        }

        $this->phpSpreadsheet->setActiveSheetIndex($nbOfSheets);

        $activeSheet = $this->phpSpreadsheet->getActiveSheet();
        $activeSheet->setTitle($title);

        $this->sheets[$nbOfSheets] = $activeSheet;

        return $activeSheet;
    }

    /**
     * @param $filename
     * @return mixed
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function getStreamedResponse($filename)
    {
        $writer = $this->phpSpreadsheetFactory->createWriter($this->phpSpreadsheet, $this->phpExcelType);

        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );

        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}