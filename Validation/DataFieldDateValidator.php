<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataFieldDateValidator extends DataFieldValidator
{
    /**
     * DataFieldDateValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param array $params
     */
    public function __construct(ValidatorInterface $frameworkValidator, array $params)
    {
        parent::__construct($frameworkValidator, $params);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ValidationResult
     */
    public function validateValue($key, $value)
    {
        $valRes = new ValidationResult($this->severity);
        $valRes->setValid();
        
        if (!$this->nullable) {
            $blankConstraint = new Assert\NotBlank();
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $blankConstraint));
        }

        if ($valRes->isValid() && $value != '' ) {
            $date_constraint = new Assert\Date();
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $date_constraint));
        }
        
        return $valRes;
    }

    /**
     * print validator information
     *
     * @return string
     */
    public function __toString()
    {
        $valInfo = array();

        $valInfo[] = parent::__toString();

        return 'DateValidator(' . join(', ', $valInfo) . ')';
    }
    
}