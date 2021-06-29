<?php

namespace Hboie\DataIOBundle\Tests\Mapper;

use Hboie\DataIOBundle\Mapper\ValidationMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;

class ValidationMapper1Test extends KernelTestCase
{
    /**
     * @var DataValidator
     */
    protected $dataValidator;

    protected function setUp() : void
    {
        self::bootKernel();

        $this->dataValidator = static::$kernel->getContainer()->get('dataio.data.validator');
    }
    
    /**
     * @group dataio.mapper
     */
    public function testBasic()
    {
        $validationMapper = new ValidationMapper($this->dataValidator);

        $validationMapper->loadXmlMapping(__DIR__.'/../Resources/config/test_basic.mapping.xml');

        $testObject = new DataFieldTestObject();
        $testObject->setStringValue('Das ist ein Test');
        $testObject->setDecimalValue('25.07');
        $testObject->setDateValue('2015-01-05');

        // test validation

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertTrue($valResList->isValid());

        $testObject->setStringValue('');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), 'INFO: The value "StringValue" should not be blank.');
        $this->assertEquals($valResList->getJudge(), DataValidator::INFO);

        $testObject->setStringValue('Das ist ein Test');
        $testObject->setDecimalValue('25.07234');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), 'The value "DecimalValue" has not the correct scale.');
        $this->assertEquals($valResList->getJudge(), DataValidator::NOT_VALID);

        $testObject->setDecimalValue('25.07');
        $testObject->setDateValue('05.01.2015');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), 'WARNING: The value "DateValue" is not a valid date.');
        $this->assertEquals($valResList->getJudge(), DataValidator::WARNING);

        $testObject->setDecimalValue('25.07234');

        $valResList = $validationMapper->validateObject($testObject);

        $errMsg = 'WARNING: The value "DateValue" is not a valid date, ';
        $errMsg .= 'the value "DecimalValue" has not the correct scale.';
        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), $errMsg);
        $this->assertEquals($valResList->getJudge(), DataValidator::NOT_VALID);
    }
}