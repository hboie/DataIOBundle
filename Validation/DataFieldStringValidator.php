<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataFieldStringValidator extends DataFieldValidator
{
    /**
     * @var integer $length
     */
    private $length;

    /**
     * DataFieldStringValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param array $params
     */
    public function __construct(ValidatorInterface $frameworkValidator, array $params)
    {
        parent::__construct($frameworkValidator, $params);

        if(isset($params['length'])) {
            $this->length = $params['length'];
        } else {
            $this->length = -1;
        }
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
            if ($this->length > 0) {
                $lengthConstraint = new Assert\Length(['max' => $this->length]);
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $lengthConstraint));
            }
        }
        
        return $valRes;
    }

    /**
     * @return int
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int $length
     */
    public function setLength($length)
    {
        $this->length = $length;
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
        
        $valInfo[] = 'length: ' . $this->length;

        return 'StringValidator(' . join(', ', $valInfo) . ')';
    }
}