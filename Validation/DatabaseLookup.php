<?php

namespace Hboie\DataIOBundle\Validation;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Hboie\DataIOBundle\Validation\DataValidator;
use Exception;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Symfony\Component\PropertyAccess\PropertyAccess;

class DatabaseLookup
{
    /**
     * @var ObjectManager $entityManager
     */
    private $entityManager;

    /**
     * @var EntityRepository $lookupRepository
     */
    private $lookupRepository;

    /**
     * @var string $lookupField
     */
    private $lookupField;

    /**
     * @var string $lookupValue
     */
    private $lookupValue;

    /**
     * @var string $targetField
     */
    private $targetField;

    /**
     * @var string $targetValue
     */
    private $targetValue;

    /**
     * @var array $cond
     */
    private $cond;

    /**
     * @var array $pendingMapping
     */
    private $pendingMapping;

    public function __construct(ObjectManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cond = array();
        $this->pendingMapping = array();
    }

    /**
     * @param string $field
     * @param string $lookupField
     * @param string $lookupValue
     * @return mixed|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function lookup($targetField, $lookupField, $lookupValue)
    {
        if (isset($this->lookupRepository)) {
            $qb = $this->lookupRepository->createQueryBuilder('m');

            $qb->where('m.' . $this->lookupField . ' = ?1');
            $qb->andWhere('m.' . $this->lookupValue . ' = ?2');
            $qb->andWhere('m.' . $this->targetField . ' = ?3');

            foreach ($this->cond as $key => $value) {
                $qb->andWhere("m." . $key . " = '" . $value . "'");
            }

            $qb->setParameters([
                1 => $lookupField,
                2 => $lookupValue,
                3 => $targetField
            ]);

            $result = $qb->getQuery()->setMaxResults(1)->getOneOrNullResult();

            if ($result) {
                $accessor = PropertyAccess::createPropertyAccessor();
                return $accessor->getValue($result, $this->targetValue);
            } else {
                $this->addPendingMapping($targetField, $lookupField, $lookupValue);
                return null;
            }
        } else {
            $this->addPendingMapping($targetField, $lookupField, $lookupValue);
            return null;
        }
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function addCondition($field, $value)
    {
        $this->cond[$field] = $value;
    }

    /**
     * @return bool
     */
    public function isRegistered()
    {
        if (isset($this->lookupRepository)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $attrib
     */
    public function setLookupAttributes($attrib)
    {
        $this->lookupField = '';
        $this->lookupValue = '';
        $this->targetField = '';
        $this->targetValue = '';

        if(isset($attrib['lookup-prefix'])) {
            $this->lookupField = $attrib['lookup-prefix'] . 'Field';
            $this->lookupValue = $attrib['lookup-prefix'] . 'Value';
        }

        if(isset($attrib['lookup-field'])) {
            $this->lookupField = $attrib['lookup-field'];
        }
        if(isset($attrib['lookup-value'])) {
            $this->lookupValue = $attrib['lookup-value'];
        }

        if(isset($attrib['target-prefix'])) {
            $this->targetField = $attrib['target-prefix'] . 'Field';
            $this->targetValue = $attrib['target-prefix'] . 'Value';
        }

        if(isset($attrib['target-field'])) {
            $this->targetField = $attrib['target-field'];
        }
        if(isset($attrib['target-value'])) {
            $this->targetValue = $attrib['target-value'];
        }

        if(isset($attrib['lookup-entity'])) {
            $this->registerLookupEntity($attrib['lookup-entity']);
        }
    }

    /**
     * @param string $lookupEntity
     * @throws Exception
     */
    public function registerLookupEntity($lookupEntity)
    {
        // take care the parameter is a string
        $lookupEntityStr = (string) $lookupEntity;

        try {
            $this->lookupRepository = $this->entityManager->getRepository($lookupEntityStr);
        } catch (Exception $e) {
            if ($e instanceof MappingException) {
                // try to find entity in AppBundle
                $this->lookupRepository = $this->entityManager->getRepository('AppBundle:' . $lookupEntityStr);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $field
     * @param string $lookupField
     * @param string $lookupValue
     */
    public function addPendingMapping($field, $lookupField, $lookupValue)
    {
        $hash = hash('sha256', $field . $lookupField . $lookupValue);
        
        if ( !isset($this->pendingMapping[$hash]) ) {
            $this->pendingMapping[$hash] = [
                $this->lookupField => $lookupField,
                $this->lookupValue => $lookupValue,
                $this->targetField => $field
                ];
        }
    }
    
    public function getPendingMapping()
    {
        return $this->pendingMapping;
    }
}