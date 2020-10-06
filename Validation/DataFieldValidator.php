<?php

namespace Hboie\DataIOBundle\Validation;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Hboie\DataIOBundle\Validation\ValidationResult;

abstract class DataFieldValidator
{
    const ERROR = ValidationResult::ERROR;
    const WARNING = ValidationResult::WARNING;
    const INFO = ValidationResult::INFO;
    const DROP_DATASET = ValidationResult::DROP_DATASET;
    
    const PENDING = ValidationResult::PENDING;

    /**
     * @var string $severity
     */
    protected $severity;

    /**
     * @var bool $nullable
     */
    protected $nullable;

    /**
     * @var ValidatorInterface $frameworkValidator
     */
    protected $frameworkValidator;

    /**
     * @var PropertyAccess $accessor
     */
    protected $accessor;

    /**
     * DataFieldValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param array $params
     */
    public function __construct($frameworkValidator, $params)
    {
        $this->frameworkValidator = $frameworkValidator;
        $this->accessor = PropertyAccess::createPropertyAccessor();
        
        if (isset($params['nullable'])) {
            if (is_bool($params['nullable'])) {
                $this->nullable = $params['nullable'];
            } else if (strtolower($params['nullable']) == 'false') {
                $this->nullable = false;
            } else {
                $this->nullable = true;
            }
        } else {
            $this->nullable = true;
        }
        
        if(isset($params['severity'])) {
            if (strtolower($params['severity']) == 'information'
                || strtolower($params['severity']) == 'info') {
                $this->severity = DataFieldValidator::INFO;
            } else if (strtolower($params['severity']) == 'warning'
                || strtolower($params['severity']) == 'warn') {
                $this->severity = DataFieldValidator::WARNING;
            } else if (strtolower($params['severity']) == 'error'
                || strtolower($params['severity']) == 'err') {
                $this->severity = DataFieldValidator::ERROR;
            } else if (strtolower($params['severity']) == 'drop_dataset'
                || strtolower($params['severity']) == 'drop') {
                $this->severity = DataFieldValidator::DROP_DATASET;
            } else {
                $this->severity = $params['severity'];
            }
        } else {
            $this->severity = '';
        }
    }

    /**
     * @return string
     */
    public function getSeverity()
    {
        return $this->severity;
    }

    /**
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = $severity;
    }

    /**
     * @return boolean
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * @param boolean $nullable
     */
    public function setNullable($nullable)
    {
        $this->nullable = $nullable;
    }

    /**
     * @param string $key
     * @param object $object
     *
     * @return ValidationResult
     */
    public function validate($key, $object)
    {
        return $this->validateObject($key, $object);
    }

    /**
     * @param string $key
     * @param object $object
     *
     * @return ValidationResult
     */
    public function validateObject($key, $object)
    {
        $value = $this->accessor->getValue($object, $key);
        
        return $this->validateValue($key, $value);
    }

    /**
     * @param string $key
     * @param mixed $object
     *
     * @return ValidationResult
     */
    abstract public function validateValue($key, $value);
    
    /**
     * print basic validator information
     * 
     * @return string
     */
    public function __toString()
    {
        $valInfo = array();
        if($this->nullable) {
            $valInfo[] = 'nullable: true';
        } else {
            $valInfo[] = 'nullable: false';
        }
        
        if($this->severity != '') {
            $valInfo[] = 'severity: ' . $this->severity;
        }
        
        return join(', ', $valInfo);
    }
}