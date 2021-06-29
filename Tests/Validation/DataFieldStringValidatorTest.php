<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataFieldStringValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Hboie\DataIOBundle\Tests\Validation\DataFieldTestObject;
use Hboie\DataIOBundle\Validation\DataValidator;

class DataFieldStringValidatorTest extends KernelTestCase
{
    /**
     * @var DataFieldValidatorFactory
     */
    protected $validatorFactory;

    protected function setUp() : void
    {
        self::bootKernel();

        $this->validatorFactory = static::$kernel->getContainer()->get('dataio.validator.factory');
    }
    
    /**
     * @group dataio.validator
     */
    public function testNullable()
    {
        $stringValidator = $this->validatorFactory->createStringValidator([
            'nullable' => false
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('Hello World');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value should not be blank.');

        $stringValidator->setNullable(true);
        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testLength()
    {
        $stringValidator = $this->validatorFactory->createStringValidator([
            'nullable' => true,
            'length' => 5
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('Hello World');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is too long. It should have 5 characters or less.');

        $testObject->setStringValue('Hello');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testFactory()
    {
        $stringValidator = $this->validatorFactory->createValidator([
            'type' => DataValidator::STRING_VAL,
            'nullable' => true,
            'length' => 5
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('Hello World');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is too long. It should have 5 characters or less.');

        $testObject->setStringValue('Hello');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testSeverity()
    {
        $stringValidator = $this->validatorFactory->createStringValidator([
            'nullable' => false,
            'length' => 5,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);

        $stringValidator = $this->validatorFactory->createStringValidator([
            'nullable' => false,
            'length' => 5,
            'severity' => DataFieldValidator::WARNING
        ]);

        $testObject->setStringValue('Hello World');

        $errorList = $stringValidator->validate('stringValue', $testObject);

        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);
    }
}