<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Hboie\DataIOBundle\Validation\ValidationResultList;

class DataValidator
{
    const ERROR = ValidationResult::ERROR;
    const WARNING = ValidationResult::WARNING;
    const INFO = ValidationResult::INFO;

    const VALID = ValidationResult::VALID;
    const NOT_VALID = ValidationResult::NOT_VALID;
    const NOT_VALIDATED = ValidationResult::NOT_VALIDATED;
    
    const PENDING = ValidationResult::PENDING;

    const DATE_VAL = "date";
    const STRING_VAL = "string";
    const DECIMAL_VAL = "decimal";
    const DATABASE_VAL = "database";

    /**
     * @var DataFieldValidatorFactory
     */
    private $validatorFactory;

    /**
     * @var array
     */
    private $dataValidators;

    public function __construct(DataFieldValidatorFactory $validatorFactory)
    {
        $this->validatorFactory = $validatorFactory;

        $this->dataValidators = array();
    }

    /**
     * @param string $key
     * @param object $object
     * @return ValidationResult
     */
    public function validate($key, $object)
    {
        if (isset($this->dataValidators[$key])) {
            return $this->dataValidators[$key]->validate($key, $object);
        } else {
            $valRes = new ValidationResult();
            $valRes->setNotValid('key "' . $key . '" not valid - possible values: '
                . join(', ', $this->getKeys()));

            return $valRes;
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ValidationResult
     */
    public function validateValue($key, $value)
    {
        if (isset($this->dataValidators[$key])) {
            return $this->dataValidators[$key]->validateValue($key, $value);
        } else {
            $valRes = new ValidationResult();
            $valRes->setNotValid('key "' . $key . '" not valid - possible values: '
                . join(', ', $this->getKeys()));

            return $valRes;
        }
    }

    /**
     * @param object $object
     * @return ValidationResult
     */
    public function validateObject($object)
    {
        $valResList = new ValidationResultList();
        
        foreach ($this->dataValidators as $key => $validator) {
            /** @var DataFieldValidator $validator */
            $valResList->addValidationResult($key, $validator->validate($key, $object));
        }
        
        return($valResList);
    }
    
    /**
     * @param string $key
     * @param array $params
     */
    public function addValidator($key, array $params)
    {
        $this->dataValidators[$key] = $this->validatorFactory->createValidator($params);
    }

    /**
     * DatabaseLookup
     */
    public function getDatabaseLookup()
    {
        return $this->validatorFactory->getDatabaseLookup();
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        $ret_array = array();
        foreach ($this->dataValidators as $key => $validator) {
            $ret_array[] = $key;
        }

        return $ret_array;
    }

    /**
     * print validator information
     * 
     * @return string
     */
    public function __toString()
    {
        $valInfo = array();

        foreach($this->dataValidators as $key => $validator) {
            /** @var DataFieldValidator $validator */
            $valInfo[] = $key . ": " . $validator->__toString();
        }
        
        return join("\n", $valInfo);
    }
}