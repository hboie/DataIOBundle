<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;

class DataValidator1Test extends KernelTestCase
{
    /**
     * @var DataValidator
     */
    protected $dataValidator;

    protected function setUp()
    {
        self::bootKernel();

        $this->dataValidator = static::$kernel->getContainer()->get('dataio.data.validator');
    }
    
    /**
     * @group dataio.validator
     */
    public function testNullable()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::STRING_VAL,
            'nullable' => false
        ]);

        $this->dataValidator->addValidator('decimalValue', [
            'type' => DataValidator::DECIMAL_VAL,
            'nullable' => false
        ]);

        $this->dataValidator->addValidator('dateValue', [
            'type' => DataValidator::DATE_VAL,
            'nullable' => true
        ]);

        $testObject = new DataFieldTestObject();

        // test string validator

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('Hello World');

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value should not be blank.');

        // test decimal validator

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setDecimalValue('25.07');

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setDecimalValue('');

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value should not be blank.');

        // test date validator

        $errorList = $this->dataValidator->validate('dateValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setDecimalValue('2015-01-05');

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testValidation()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::STRING_VAL,
            'nullable' => true,
            'length' => 5
        ]);

        $this->dataValidator->addValidator('decimalValue', [
            'type' => DataValidator::DECIMAL_VAL,
            'nullable' => false,
            'precision' => 9,
            'scale' => 2,
            'severity' => DataValidator::ERROR
        ]);

        $this->dataValidator->addValidator('dateValue', [
            'type' => DataValidator::DATE_VAL,
            'nullable' => true
        ]);

        $testObject = new DataFieldTestObject();

        // test string validator

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('Hello World');

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is too long. It should have 5 characters or less.');

        $testObject->setStringValue('Hello');

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        // test decimal validator

        $testObject->setDecimalValue('1.54');

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setDecimalValue('3451.45454');

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct scale.');

        $testObject->setDecimalValue('3453531.45454');

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $errMsg = 'This value has not the correct scale, this value has not the correct precision.';
        $this->assertEquals($errorList->getMessage(), $errMsg);
        
        // test date validator

        $testObject->setDateValue('2015-01-05');

        $errorList = $this->dataValidator->validate('dateValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setDateValue('2034-05-23');

        $errorList = $this->dataValidator->validate('dateValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setDateValue('2014-13-23');

        $errorList = $this->dataValidator->validate('dateValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');
    }

    /**
     * @group dataio.validator
     */
    public function testObjectValidation()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::STRING_VAL,
            'severity' => DataValidator::ERROR,
            'nullable' => false,
            'length' => 5
        ]);

        $this->dataValidator->addValidator('decimalValue', [
            'type' => DataValidator::DECIMAL_VAL,
            'nullable' => false,
            'precision' => 9,
            'scale' => 2
        ]);

        $this->dataValidator->addValidator('dateValue', [
            'type' => DataValidator::DATE_VAL,
            'nullable' => true
        ]);

        $testObject = new DataFieldTestObject();
        $testObject->setDecimalValue('3451.45454');
        $testObject->setDateValue('2015-05-01');

        $valResList = $this->dataValidator->validateObject($testObject);
        $errMsg = 'ERROR: The value "stringValue" should not be blank, ';
        $errMsg .= 'the value "decimalValue" has not the correct scale.';
        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getJudge(), DataValidator::ERROR);
        $this->assertEquals($valResList->getMessage(), $errMsg);

        $stringValRes = $valResList->getValidationResult('dateValue');
        $this->assertTrue($stringValRes->isValid());
        $this->assertEquals($stringValRes->getJudge(), DataValidator::VALID);
        $this->assertEquals($stringValRes->getMessage(), '');

        $stringValRes = $valResList->getValidationResult('decimalValue');
        $this->assertFalse($stringValRes->isValid());
        $this->assertEquals($stringValRes->getJudge(), DataValidator::NOT_VALID);
        $this->assertEquals($stringValRes->getMessage(), 'This value has not the correct scale.');

        $this->assertArrayHasKey('stringValue', $valResList->getValidationResults());
        $this->assertArrayHasKey('decimalValue', $valResList->getValidationResults());
        $this->assertArrayHasKey('dateValue', $valResList->getValidationResults());
    }


        /**
     * @group dataio.validator
     */
    public function testSeverity()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::STRING_VAL,
            'nullable' => false
        ]);

        $this->dataValidator->addValidator('decimalValue', [
            'type' => DataValidator::DECIMAL_VAL,
            'nullable' => false,
            'severity' => DataValidator::ERROR
        ]);

        $this->dataValidator->addValidator('dateValue', [
            'type' => DataValidator::DATE_VAL,
            'nullable' => false,
            'severity' => DataValidator::WARNING
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $this->dataValidator->validate('stringValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataValidator::NOT_VALID);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::INFO);

        $errorList = $this->dataValidator->validate('decimalValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataValidator::ERROR);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::VALID);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::INFO);

        $errorList = $this->dataValidator->validate('dateValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::VALID);
        $this->assertNotEquals($errorList->getJudge(), DataValidator::INFO);
    }
}