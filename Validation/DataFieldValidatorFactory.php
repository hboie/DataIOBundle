<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Hboie\DataIOBundle\Validation\DataFieldStringValidator;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Validation\DatabaseLookup;

class DataFieldValidatorFactory
{
    /**
     * @var ValidatorInterface $frameworkValidator
     */
    private $frameworkValidator;

    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var DatabaseLookup $databaseLookup
     */
    private $databaseLookup;

    /**
     * DataFieldValidatorFactory constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param ObjectManager $entityManager
     * @param DatabaseLookup $databaseLookup
     */
    public function __construct($frameworkValidator, $entityManager, $databaseLookup)
    {
        $this->frameworkValidator = $frameworkValidator;
        $this->entityManager = $entityManager;
        $this->databaseLookup = $databaseLookup;
    }

    /**
     * @param array $params
     * @return DataFieldStringValidator
     */
    public function createStringValidator($params)
    {
        $stringValidator = new DataFieldStringValidator($this->frameworkValidator, $params);

        return $stringValidator;
    }

    /**
     * @param array $params
     * @return DataFieldDecimalValidator
     */
    public function createDecimalValidator($params)
    {
        $decimalValidator = new DataFieldDecimalValidator($this->frameworkValidator, $params);

        return $decimalValidator;
    }

    /**
     * @param array $params
     * @return DataFieldDateValidator
     */
    public function createDateValidator($params = [])
    {
        $dateValidator = new DataFieldDateValidator($this->frameworkValidator, $params);

        return $dateValidator;
    }

    /**
     * @param array $params
     * @return DataFieldDateValidator
     */
    public function createDatabaseValidator($params = [])
    {
        $databaseValidator = new DataFieldDatabaseValidator(
            $this->frameworkValidator, $this->entityManager, $this->databaseLookup, $params);

        return $databaseValidator;
    }

    /**
     * @param array $params
     * @return DataFieldDateValidator
     */
    public function createValidator($params)
    {
        if (isset($params['type'])) {
            switch ($params['type']) {
                case DataValidator::STRING_VAL:
                    return $this->createStringValidator($params);
                case DataValidator::DECIMAL_VAL:
                    return $this->createDecimalValidator($params);
                case DataValidator::DATE_VAL:
                    return $this->createDateValidator($params);
                case DataValidator::DATABASE_VAL:
                    return $this->createDatabaseValidator($params);
                default:
                    return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @return DatabaseLookup
     */
    public function getDatabaseLookup()
    {
        return $this->databaseLookup;
    }
}