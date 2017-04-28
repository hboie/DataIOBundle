<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DataFieldDecimalValidator extends DataFieldValidator
{
    /**
     * @var integer
     */
    private $precision;

    /**
     * @var integer
     */
    private $scale;

    /**
     * DataFieldDecimalValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param array $params
     */
    public function __construct(ValidatorInterface $frameworkValidator, array $params)
    {
        parent::__construct($frameworkValidator, $params);

        if(isset($params['precision'])) {
            $this->precision = $params['precision'];
        } else {
            $this->precision = -1;
        }

        if(isset($params['scale'])) {
            $this->scale = $params['scale'];
        } else {
            $this->scale = -1;
        }
    }

    /**
     * @param string $key
     * @param object $object
     * @return ValidationResult
     */
    public function validateObject($key, $object)
    {
        $value = $this->accessor->getValue($object, $key);

        // check for floating point accuracy problem and delete trailing zeros

        $valRes = new ValidationResult($this->severity);
        $valRes->setValid();

        if (!$this->nullable) {
            $blankConstraint = new Assert\NotBlank([ 'payload' => [ 'severity' => $this->severity ] ]);
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $blankConstraint));
        }

        if ($valRes->isValid() && $value != '' ) {
            $decimalConstraint = new Assert\Regex(['pattern' => '/^(?:\d*\.)?\d+$/']);
            $message = 'This value is not a valid decimal.';
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);

            // handle the floating point precision problem
            // (e.g. 6.2 may be represented as 6.199999999999999 or as 6.200000000000001)
            $phpPrecision = ini_get('precision');

            if (strlen($value) >= $phpPrecision+1) {
                // delete trailing 9999 oder 0001
                $newValue = $value;
                if (substr($newValue, -1) == 1) {
                    do {
                        $newValue = substr($newValue, 0, -1);
                    } while(substr($newValue, -1) == 0);
                } else if (substr($newValue, -1) == 9) {
                    do {
                        $newValue = substr($newValue, 0, -1);
                    } while(substr($newValue, -1) == 9);
                    $lastDigit = substr($newValue, -1);
                    $lastDigit += 1;
                    $newValue = substr($newValue, 0, -1) . $lastDigit;
                }

                if (strcmp($value, $newValue) !== 0) {
                    $this->accessor->setValue($object, $key, $newValue);
                    $value = $newValue;
                }
            }

            if ($this->scale > 0 && strpos($value, '.') !== false) {
                $pattern = '/^\d*\.?\d{0,' . $this->scale . '}$/';
                $decimalConstraint = new Assert\Regex(['pattern' => $pattern]);
                $message = 'This value has not the correct scale.';
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);
            }

            if ($this->precision > 0) {
                $length = $this->precision + 1;
                if (strpos($value, '.') === false) {
                    $length = $this->precision;
                }
                $lengthConstraint = new Assert\Length(['max' => $length]);
                $message = 'This value has not the correct precision.';
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $lengthConstraint), $message);

                if ($this->scale > 0 && strpos($value, '.') !== false) {
                    $leading = $this->precision - $this->scale;
                    $pattern = '/^\d{0,' . $leading . '}\.?\d+$/';
                    $decimalConstraint = new Assert\Regex(['pattern' => $pattern]);
                    $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);
                }
            }
        }

        return $valRes;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ValidationResult
     */
    public function validateValue($key, $value)
    {
        $value = $this->accessor->getValue($object, $key);

        // check for floating point accuracy problem and delete trailing zeros

        $valRes = new ValidationResult($this->severity);
        $valRes->setValid();

        if (!$this->nullable) {
            $blankConstraint = new Assert\NotBlank([ 'payload' => [ 'severity' => $this->severity ] ]);
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $blankConstraint));
        }

        if ($valRes->isValid() && $value != '' ) {
            $decimalConstraint = new Assert\Regex(['pattern' => '/^(?:\d*\.)?\d+$/']);
            $message = 'This value is not a valid decimal.';
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);

            if ($this->scale > 0 && strpos($value, '.') !== false) {
                $pattern = '/^\d*\.?\d{0,' . $this->scale . '}$/';
                $decimalConstraint = new Assert\Regex(['pattern' => $pattern]);
                $message = 'This value has not the correct scale.';
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);
            }

            if ($this->precision > 0) {
                $length = $this->precision + 1;
                if (strpos($value, '.') === false) {
                    $length = $this->precision;
                }
                $lengthConstraint = new Assert\Length(['max' => $length]);
                $message = 'This value has not the correct precision.';
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $lengthConstraint), $message);

                if ($this->scale > 0 && strpos($value, '.') !== false) {
                    $leading = $this->precision - $this->scale;
                    $pattern = '/^\d{0,' . $leading . '}\.?\d+$/';
                    $decimalConstraint = new Assert\Regex(['pattern' => $pattern]);
                    $valRes->convertValidationResult($this->frameworkValidator->validate($value, $decimalConstraint), $message);
                }
            }
        }

        return $valRes;
    }

    /**
     * @return int
     */
    public function getPrecision()
    {
        return $this->precision;
    }

    /**
     * @param int $precision
     */
    public function setPrecision($precision)
    {
        $this->precision = $precision;
    }

    /**
     * @return int
     */
    public function getScale()
    {
        return $this->scale;
    }

    /**
     * @param int $scale
     */
    public function setScale($scale)
    {
        $this->scale = $scale;
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

        $valInfo[] = 'precision: ' . $this->precision;
        $valInfo[] = 'scale: ' . $this->scale;

        return 'DecimalValidator(' . join(', ', $valInfo) . ')';
    }
    
}