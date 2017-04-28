<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Hboie\DataIOBundle\Validation\DataValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataFieldStringValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Hboie\DataIOBundle\Tests\Validation\DataFieldTestObject;

class DataFieldDecimalValidatorTest extends KernelTestCase
{
    /**
     * @var DataFieldValidatorFactory
     */
    protected $validatorFactory;

    protected function setUp()
    {
        self::bootKernel();

        $this->validatorFactory = static::$kernel->getContainer()->get('dataio.validator.factory');
    }

    /**
     * @group dataio.validator
     */
    public function testNullable()
    {
        $decimalValidator = $this->validatorFactory->createDecimalValidator([
            'nullable' => false
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('1.5');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $decimalValidator->setNullable(true);
        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testPrecisionAndScale()
    {
        $decimalValidator = $this->validatorFactory->createDecimalValidator([
            'nullable' => false,
            'precision' => 9,
            'scale' => -1,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('1.54');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3451.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3453531.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct precision.');

        $decimalValidator->setPrecision(-1);
        $decimalValidator->setScale(2);

        $testObject->setStringValue('1.55');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3451.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct scale.');

        $testObject->setStringValue('3453531.45');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $decimalValidator->setPrecision(5);

        $testObject->setStringValue('1.55');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('1.556');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct scale.');

        $testObject->setStringValue('2451.5');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct precision.');
    }

    /**
     * @group dataio.validator
     */
    public function testFactory()
    {
        $decimalValidator = $this->validatorFactory->createValidator([
            'type' => DataValidator::DECIMAL_VAL,
            'nullable' => false,
            'precision' => 9,
            'scale' => -1,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('1.54');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3451.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3453531.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct precision.');

        $decimalValidator->setPrecision(-1);
        $decimalValidator->setScale(2);

        $testObject->setStringValue('1.55');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('3451.45454');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct scale.');

        $testObject->setStringValue('3453531.45');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $decimalValidator->setPrecision(5);

        $testObject->setStringValue('1.55');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('1.556');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct scale.');

        $testObject->setStringValue('2451.5');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value has not the correct precision.');

        $testObject->setStringValue('1');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('1a');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('a143');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('143a5');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('143.a');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('a.5');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('.52');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('95.40000000000001');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('9.449999999999999');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testSeverity()
    {
        $decimalValidator = $this->validatorFactory->createDecimalValidator([
            'nullable' => false,
            'precision' => 9,
            'scale' => 2,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);

        $decimalValidator->setSeverity(DataFieldValidator::WARNING);

        $testObject->setStringValue('Hello World');

        $errorList = $decimalValidator->validate('stringValue', $testObject);

        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);
    }
}