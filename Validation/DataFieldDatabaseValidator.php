<?php

namespace Hboie\DataIOBundle\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Hboie\DataIOBundle\Validation\DatabaseLookup;

class DataFieldDatabaseValidator extends DataFieldValidator
{
    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var DatabaseLookup $databaseLookup
     */
    private $databaseLookup;
    
    /**
     * @var string $entity
     */
    private $entity;

    /**
     * @var string $field
     */
    private $field;

    /**
     * @var string $lookupField
     */
    private $lookupField;

    /**
     * DataFieldDatabaseValidator constructor.
     * @param ValidatorInterface $frameworkValidator
     * @param ObjectManager $entityManager
     * @param DatabaseLookup $databaseLookup
     * @param array $params
     */
    public function __construct($frameworkValidator, $entityManager, $databaseLookup, $params)
    {
        parent::__construct($frameworkValidator, $params);
        
        $this->entityManager = $entityManager;
        $this->databaseLookup = $databaseLookup;

        if(isset($params['entity'])) {
            $this->entity = $params['entity'];
        } else {
            $this->entity = '';
        }
        
        if(isset($params['field'])) {
            $this->field = $params['field'];
        } else {
            $this->field = '';
        }

        if(isset($params['lookup-field'])) {
            $this->lookupField = $params['lookup-field'];
        } else {
            $this->lookupField = '';
        }
    }

    /**
     * @param string $key
     * @param object $object
     * @return ValidationResult
     * @throws Exception
     */
    public function validateObject($key, $object)
    {
        $value = $this->accessor->getValue($object, $key);

        $valRes = new ValidationResult($this->severity);
        $valRes->setValid();

        $lookupDone = false;
        
        do {
            $revalidate = false;

            // do validation
            
            if (!$this->nullable) {
                $blankConstraint = new Assert\NotBlank();
                $valRes->convertValidationResult($this->frameworkValidator->validate($value, $blankConstraint));
            }

            if ($valRes->isValid() && $value != '') {
                // try to find entity
                try {
                    $repository = $this->entityManager->getRepository($this->entity);
                } catch (Exception $e) {
                    if ( $e instanceof MappingException ) {
                        // try to find entity in AppBundle
                        $repository = $this->entityManager->getRepository('App:' . $this->entity);
                    } else {
                        throw $e;
                    }
                }

                if (isset($repository)) {
                    $result = $repository->findBy([ $this->field => $value ]);
                    if(!$result) {
                        $message = "This value is not contained ";
                        $message .= "in field \"" . $this->field . "\" in table \"" . $this->entity . "\".";

                        $valRes->setNotValid($message);
                    }
                }
            }

            // if not valid or empty try to lookup 
            if ($this->lookupField != '') {
                if (($value == '' || !$valRes->isValid()) && $this->databaseLookup->isRegistered() && !$lookupDone) {
                    $lookupValue = $this->accessor->getValue($object, $this->lookupField);

                    $newValue = $this->databaseLookup->lookup($key, $this->lookupField, $lookupValue);

                    if ($newValue == '') {
                        $message = "The value \"" . $lookupValue . "\" cannot be found ";
                        $message .= "in field \"" . $this->lookupField . "\" in lookup-table.";
                        $valRes->setPending($message);
                    } else {
                        $message = "Replaced by value \"" . $newValue . "\" from ";
                        $message .= "field \"" . $this->lookupField . "\" from lookup-table.";
                        $valRes->setValid();
                        $valRes->addMessage($message);

                        $value = $newValue;
                        $valRes->setDataChanged(true);
                        $revalidate = true;
                    }
                    $lookupDone = true;
                }
            }
        } while ($revalidate);

        // change object if lookup was successful
        if ($valRes->isValid() && $valRes->isDataChanged()) {
            $this->accessor->setValue($object, $key, $value);
        }

        return $valRes;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ValidationResult
     * @throws Exception
     */
    public function validateValue($key, $value)
    {
        $valRes = new ValidationResult($this->severity);
        $valRes->setValid();

        // do validation

        if (!$this->nullable) {
            $blankConstraint = new Assert\NotBlank();
            $valRes->convertValidationResult($this->frameworkValidator->validate($value, $blankConstraint));
        }

        if ($valRes->isValid() && $value != '') {
            // try to find entity
            try {
                $repository = $this->entityManager->getRepository($this->entity);
            } catch (Exception $e) {
                if ( $e instanceof MappingException ) {
                    // try to find entity in AppBundle
                    $repository = $this->entityManager->getRepository('App:' . $this->entity);
                } else {
                    throw $e;
                }
            }

            if (isset($repository)) {
                $result = $repository->findBy([ $this->field => $value ]);
                if(!$result) {
                    $message = "This value is not contained ";
                    $message .= "in field \"" . $this->field . "\" in table \"" . $this->entity . "\".";

                    $valRes->setNotValid($message);
                }
            }
        }

        return $valRes;
    }

    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param string $entity
     */
    public function setEntity($entity)
    {
        $this->entity = (string) $entity;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
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

        $valInfo[] = 'entity: ' . $this->entity;
        $valInfo[] = 'field: ' . $this->field;

        return 'DatabaseValidator(' . join(', ', $valInfo) . ')';
    }
}