<?php

namespace Hboie\DataIOBundle\Tests\Validation;


class DataFieldTestObject
{
    /**
     * @var string
     */
    public $stringValue;

    /**
     * @var string
     */
    public $decimalValue;

    /**
     * @var string
     */
    public $dateValue;

    /**
     * @var string
     */
    public $databaseValue;

    /**
     * @var string
     */
    public $mockLookup;

    /**
     * @return mixed
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param mixed $stringValue
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
    }

    /**
     * @return string
     */
    public function getDecimalValue()
    {
        return $this->decimalValue;
    }

    /**
     * @param string $decimalValue
     */
    public function setDecimalValue($decimalValue)
    {
        $this->decimalValue = $decimalValue;
    }

    /**
     * @return string
     */
    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * @param string $dateValue
     */
    public function setDateValue($dateValue)
    {
        $this->dateValue = $dateValue;
    }

    /**
     * @return string
     */
    public function getDatabaseValue()
    {
        return $this->databaseValue;
    }

    /**
     * @param string $databaseValue
     */
    public function setDatabaseValue($databaseValue)
    {
        $this->databaseValue = $databaseValue;
    }

    /**
     * @return string
     */
    public function getMockLookup()
    {
        return $this->mockLookup;
    }

    /**
     * @param string $mockLookup
     */
    public function setMockLookup($mockLookup)
    {
        $this->mockLookup = $mockLookup;
    }
}