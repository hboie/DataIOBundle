<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Hboie\DataIOBundle\Validation\ValidationResult;
use Hboie\DataIOBundle\Validation\ValidationResultList;

class ValidationResultListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @group dataio.validator
     */

    public function testList()
    {
        $errorList = new ValidationResultList();

        $this->assertEquals($errorList->getJudge(), ValidationResult::NOT_VALIDATED);
        $this->assertEquals($errorList->getMessage(), '');

        $valRes1 = new ValidationResult(ValidationResult::INFO);
        $valRes1->setValid();

        $errorList->addValidationResult('value1', $valRes1);
        $this->assertEquals($errorList->getJudge(), ValidationResult::VALID);
        $this->assertEquals($errorList->getMessage(), '');

        $valRes2 = new ValidationResult(ValidationResult::INFO);
        $valRes2->setNotValid('This value is not valid.');

        $errMsg = 'INFO: The value "value2" is not valid.';
        $errorList->addValidationResult('value2', $valRes2);
        $this->assertEquals($errorList->getJudge(), ValidationResult::INFO);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes3 = new ValidationResult(ValidationResult::WARNING);
        $valRes3->setValid();

        $errorList->addValidationResult('value3', $valRes3);
        $this->assertEquals($errorList->getJudge(), ValidationResult::INFO);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes4 = new ValidationResult(ValidationResult::WARNING);
        $valRes4->setNotValid('This value is corrupted.');

        $errMsg = 'INFO: The value "value2" is not valid, ';
        $errMsg .= 'WARNING: The value "value4" is corrupted.';
        $errorList->addValidationResult('value4', $valRes4);
        $this->assertEquals($errorList->getJudge(), ValidationResult::WARNING);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes5 = new ValidationResult(ValidationResult::INFO);
        $valRes5->setNotValid('This value could be better.');

        $errMsg = 'INFO: The value "value2" is not valid, ';
        $errMsg .= 'WARNING: The value "value4" is corrupted, ';
        $errMsg .= 'INFO: The value "value5" could be better.';
        $errorList->addValidationResult('value5', $valRes5);
        $this->assertEquals($errorList->getJudge(), ValidationResult::WARNING);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes6 = new ValidationResult(ValidationResult::ERROR);
        $valRes6->setNotValid('This value must be corrected.');

        $errMsg = 'INFO: The value "value2" is not valid, ';
        $errMsg .= 'WARNING: The value "value4" is corrupted, ';
        $errMsg .= 'INFO: The value "value5" could be better, ';
        $errMsg .= 'ERROR: The value "value6" must be corrected.';
        $errorList->addValidationResult('value6', $valRes6);
        $this->assertEquals($errorList->getJudge(), ValidationResult::ERROR);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes7 = new ValidationResult(ValidationResult::WARNING);
        $valRes7->setValid();

        $errorList->addValidationResult('value7', $valRes7);
        $this->assertEquals($errorList->getJudge(), ValidationResult::ERROR);
        $this->assertEquals($errorList->getMessage(), $errMsg);

        $valRes8 = new ValidationResult();
        $valRes8->setNotValid();

        $errorList->addValidationResult('value8', $valRes8);
        $this->assertEquals($errorList->getJudge(), ValidationResult::ERROR);
        $this->assertEquals($errorList->getMessage(), $errMsg);
    }
}