<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Hboie\DataIOBundle\Validation\DataValidator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataFieldStringValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Hboie\DataIOBundle\Tests\Validation\DataFieldTestObject;

class DataFieldDateValidatorTest extends KernelTestCase
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
        $dateValidator = $this->validatorFactory->createDateValidator([
            'nullable' => false]
        );

        $testObject = new DataFieldTestObject();

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('2015-01-05');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value should not be blank.');

        $dateValidator->setNullable(true);
        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testDate()
    {
        $dateValidator = $this->validatorFactory->createDateValidator();

        $testObject = new DataFieldTestObject();

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2015-01-05');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2034-05-23');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2034-13-23');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');

        $testObject->setStringValue('2034-10-35');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');

        $testObject->setStringValue('23.05.2015');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');
    }

    /**
     * @group dataio.validator
     */
    public function testFactory()
    {
        $dateValidator = $this->validatorFactory->createValidator([
            'type' => DataValidator::DATE_VAL
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2015-01-05');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2034-05-23');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('2034-13-23');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');

        $testObject->setStringValue('2034-10-35');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');

        $testObject->setStringValue('23.05.2015');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value is not a valid date.');
    }

    /**
     * @group dataio.validator
     */
    public function testSeverity()
    {
        $dateValidator = $this->validatorFactory->createDateValidator([
            'nullable' => false,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);

        $dateValidator = $this->validatorFactory->createDateValidator([
            'nullable' => false,
            'severity' => DataFieldValidator::WARNING
        ]);

        $testObject->setStringValue('NoDate');

        $errorList = $dateValidator->validate('stringValue', $testObject);

        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);
    }
}