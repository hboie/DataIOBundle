<?php

namespace Hboie\DataIOBundle\Export;

use Liuggio\ExcelBundle\LiuggioExcelBundle;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ExcelFileCreator
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
     * @var string $phpExcelType
     */
    private $phpExcelType;

    /**
     * @var array $sheets
     */
    private $sheets;

    /**
     * ExcelFileLoader constructor.
     * @param \Liuggio\ExcelBundle\Factory $phpExcelFactory
     */
    public function __construct($phpExcelFactory)
    {
        $this->phpExcelFactory = $phpExcelFactory;

        $this->phpExcelObj = $this->phpExcelFactory->createPHPExcelObject();
        $this->phpExcelObj->getProperties()->setCreator("Conet");
        $this->phpExcelType = 'Excel2007';

        $this->sheets = array();
    }
    /**
     * @param string $title
     * @return \PHPExcel_Worksheet
     */
    public function addSheet($title)
    {
        $nbOfSheets = count($this->sheets);

        if ($nbOfSheets > 0) {
            $this->phpExcelObj->createSheet($nbOfSheets);
        }

        $this->phpExcelObj->setActiveSheetIndex($nbOfSheets);

        $activeSheet = $this->phpExcelObj->getActiveSheet();
        $activeSheet->setTitle($title);

        $this->sheets[$nbOfSheets] = $activeSheet;

        return $activeSheet;
    }

    /**
     * @param string $filename
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function getStreamedResponse($filename)
    {
        $writer = $this->phpExcelFactory->createWriter($this->phpExcelObj, $this->phpExcelType);

        $response = $this->phpExcelFactory->createStreamedResponse($writer);

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