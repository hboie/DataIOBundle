<?php

namespace Hboie\DataIOBundle\Validation;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationResult
{
    const VALID = "VALID";
    const NOT_VALID = "NOT_VALID";
    const NOT_VALIDATED = "";

    const ERROR = "ERROR";
    const WARNING = "WARNING";
    const INFO = "INFO";
    const DROP_DATASET = "DROP_DATASET";

    const PENDING = "PENDING";

    /**
     * @var string
     */
    private $judge;

    /**
     * @var string
     */
    private $message;

    /**
     * @var string
     */
    private $severity;

    /**
     * @var string
     */
    private $joinString;

    /**
     * @var bool
     */
    private $dataChanged;

    public function __construct($severity = '')
    {
        $this->judge = ValidationResult::NOT_VALIDATED;
        $this->message = '';
        $this->severity = $severity;
        $this->joinString = ', ';
        $this->dataChanged = false;
    }

    /**
     * @return string
     */
    public function getJudge()
    {
        return $this->judge;
    }

    /**
     * @param string $judge
     */
    public function setJudge($judge)
    {
        $this->judge = $judge;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }


    /**
     * @return string
     */
    public function getJoinString()
    {
        return $this->joinString;
    }

    /**
     * @param string $joinString
     */
    public function setJoinString($joinString)
    {
        $this->joinString = $joinString;
    }

    /**
     * @param ConstraintViolationListInterface $violationList
     */
    public function convertValidationResult($violationList, $message = '')
    {
        if ( count($violationList) > 0 ) {
            if ($message != '') {
                $this->setNotValid($message);
            } else {
                $messageArray = array();
                foreach ($violationList as $violation) {
                    $messageArray[] = $violation->getMessage();
                }
                $this->setNotValid(join($this->joinString, $messageArray));
            }
        }
    }

    /**
     * @param string $message
     */
    public function setNotValid($message = '')
    {
        if ($this->severity != '') {
            $this->judge = $this->severity;
        } else {
            $this->judge = ValidationResult::NOT_VALID;
        }

        if ($message != '') {
            $this->addMessage($message);
        }
    }

    /**
     * @param string $message
     */
    public function setPending($message = '')
    {
        $this->judge = ValidationResult::PENDING;

        if ($message != '') {
            $this->addMessage($message);
        }
    }

    public function addMessage($message)
    {
        if ($message != '') {
            if ($this->message == '') {
                $this->message = $message;
            } else {
                if(substr($this->message, -1) == '.') {
                    $this->message = rtrim($this->message, '.');
                }
                $this->message .= $this->joinString;
                if ( strtoupper(substr($message, 0, 1)) == substr($message, 0, 1)
                    && strtoupper(substr($message, 0, 2)) != substr($message, 0, 2) ) {
                    $this->message .= strtolower(substr($message, 0, 1)) . substr($message, 1);
                } else {
                    $this->message .= $message;
                }
            }
        }
    }

    public function setValid($judge = '')
    {
        if ($judge == ValidationResult::INFO) {
            $this->judge = ValidationResult::INFO;
        } else if ($judge == ValidationResult::WARNING) {
            $this->judge = ValidationResult::WARNING;
        } else {
            $this->judge = ValidationResult::VALID;
        }
    }
    
    public function isValid()
    {
        if ($this->judge == ValidationResult::VALID) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return boolean
     */
    public function isDataChanged()
    {
        return $this->dataChanged;
    }

    /**
     * @param boolean $dataChanged
     */
    public function setDataChanged($dataChanged)
    {
        $this->dataChanged = $dataChanged;
    }
}