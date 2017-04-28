<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\ValidationResult;

class ValidationResultList extends ValidationResult
{
    /**
     * @var array
     */
    public $resultList;
    
    public function __construct()
    {
        parent::__construct();
        
        $this->resultList = array();
    }

    /**
     * adds the ValidationResult object for a validated value identified by key, update common judge and message
     * 
     * @param string $key
     * @param ValidationResult $valRes
     */
    public function addValidationResult($key, $valRes)
    {
        $this->resultList[$key] = $valRes;

        $this->addValidationResultJudge($valRes);

        $this->addValidationResultMessage($key, $valRes);
        
        if ($valRes->isDataChanged()) {
            $this->setDataChanged(true);
        }
    }

    /**
     * @param ValidationResult $valRes
     */
    private function addValidationResultJudge($valRes)
    {
        $curJudge = $this->getJudge();
        $valResJudge = $valRes->getJudge();

        if ( $valResJudge != ValidationResult::NOT_VALIDATED ) {
            if ( $curJudge == ValidationResult::NOT_VALIDATED || $curJudge  == DataValidator::VALID
                || ( $curJudge == ValidationResult::INFO &&
                    ( $valResJudge == ValidationResult::WARNING || $valResJudge == ValidationResult::ERROR
                        || $valResJudge == ValidationResult::NOT_VALID ) )
                || ( $curJudge == ValidationResult::WARNING &&
                    ( $valResJudge == ValidationResult::ERROR || $valResJudge == ValidationResult::NOT_VALID ) )
                || ( $curJudge == ValidationResult::NOT_VALID &&
                    $valResJudge == ValidationResult::ERROR ) ) {
                $this->setJudge($valResJudge);
            }
        }
    }

    /**
     * @param string $key
     * @param ValidationResult $valRes
     */
    private function addValidationResultMessage($key, $valRes)
    {
        $valResMessage = $valRes->getMessage();
        $valResJudge = $valRes->getJudge();
        
        if( $valResMessage != '' ) {
            $thisValue = 'The value "' . $key . '"';
            $keyMessage = str_replace('This value', $thisValue, $valResMessage);

            $thisValue = 'the value "' . $key . '"';
            $keyMessage = str_replace('this value', $thisValue, $keyMessage);

            if ( $valResJudge == ValidationResult::INFO || $valResJudge == ValidationResult::WARNING
                || $valResJudge == ValidationResult::ERROR ) {
                $keyMessage = $valResJudge . ": " . $keyMessage;
            }
            
            $this->addMessage($keyMessage);
        }
    }

    /**
     * @param $key
     * @return ValidationResult
     */
    public function getValidationResult($key)
    {
        if (isset($this->resultList[$key])) {
            return $this->resultList[$key];
        } else {
            $valRes = new ValidationResult();
            $valRes->addMessage('unknown key');
            return $valRes;
        }
    }

    /**
     * @return array
     */
    public function getValidationResults()
    {
        return $this->resultList;
    }
}