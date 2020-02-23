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
     * @var array $validDateFormats
     */
    protected $dateFormatsArray;

    /**
     * DataFieldDateValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param array $params
     */
    public function __construct(ValidatorInterface $frameworkValidator, array $params)
    {
        if(isset($params['dateformat'])) {
            $formatsString = $params['dateformat'];
            $this->dateFormatsArray = explode('|', $formatsString);
        } else {
            $this->dateFormatsArray = array();
            array_push($this->dateFormatsArray, 'd-m-Y');
        }

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
            $isValidDate = false;
            foreach ( $this->dateFormatsArray as $dateFormat ) {
                // check if value is valid
                $d = \DateTime::createFromFormat($dateFormat, $value);
                if ( $d && $d->format( $dateFormat ) === $value ) {
                    $isValidDate = true;
                }
            }
            if ( ! $isValidDate ) {
                $valRes->setNotValid('key "' . $key . '" not valid - no valid time format "' . implode(', ', $this->dateFormatsArray) . '"');
            }
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