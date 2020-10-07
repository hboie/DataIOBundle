<?php

namespace Hboie\DataIOBundle\Mapper;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImportMapper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var array
     */
    private $map;

    /**
     * @var array
     */
    private $format;

    /**
     * @var array
     */
    private $dateFormat;

    /**
     * @var array
     */
    private $keyFields;

    /**
     * @var mixed
     */
    private $object;

    /**
     * @var PropertyAccess $accessor
     */
    protected $accessor;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->map = array();
        $this->format = array();
        $this->keyFields = array();

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $filename
     */
    public function loadXmlMapping($filename)
    {
        $xml = new \SimpleXmlElement(file_get_contents($filename));
        foreach ($xml->field as $field) {
            $fieldAttrib = $field->attributes();

            $notarget = false;
            if (isset($fieldAttrib['notarget'])) {
                $notarget = (string)$fieldAttrib['notarget'];
                if (strtolower($notarget) == 'true') {
                    $notarget = true;
                }
            }

            $fieldName = "";
            if (isset($fieldAttrib['name'])) {
                $fieldName = (string)$fieldAttrib['name'];
            }

            $target = "";
            if (isset($fieldAttrib['target'])) {
                $target = (string)$fieldAttrib['target'];
            }

            $format = "";
            if (isset($fieldAttrib['format'])) {
                $format = (string)$fieldAttrib['format'];
            }

            if (isset($fieldAttrib['key'])) {
                if ( $fieldAttrib['key'] == "true" ) {
                    array_push($this->keyFields, $fieldName);
                }
            }

            if (!$notarget && $fieldName != "") {
                if ($target == "") {
                    $target = $fieldName;
                }
                $this->map[$fieldName] = $target;
                $this->format[$fieldName] = $format;

                foreach ($field->children() as $child) {
                    /** @var \SimpleXMLElement $child */
                    if ($child->getName() == 'validate') {
                        $childAttrib = $child->attributes();
                        if (isset($childAttrib['dateformat'])) {
                            $this->dateFormat[$fieldName] = (string)$childAttrib['dateformat'];
                        }
                    }
                }
            }
        }
    }

    /**
     * @param mixed $source
     * @return bool
     */
    public function insertValues($source)
    {
        foreach ($this->map as $sourceKey => $targetKey) {
            $getterMethod = 'get' . $sourceKey;
            $setter_method = 'set' . $targetKey;
            if (!method_exists($source, $getterMethod)) {
                return false;
            }

            if (!method_exists($this->object, $setter_method)) {
                return false;
            }

            $value = $source->$getterMethod();
            $targetValue = $value;
            if($this->format[$sourceKey] == 'date') {
                // use given dateformats to import date
                $targetValue = null;

                if ( isset($this->dateFormat[$sourceKey]) ) {
                    foreach ( explode('|',$this->dateFormat[$sourceKey]) as $dateFormat ) {
                        $d = \DateTime::createFromFormat($dateFormat, $value);
                        if ( $d != null ) {
                            if ( $d->format( $dateFormat ) === $value ) {
                                $targetValue = new \DateTime( $d->format('Y-m-d') );
                                break;
                            }
                        }
                    }
                }
            }

            if ( $targetValue != null ) {
                $this->object->$setter_method($targetValue);
            }
        }
        return true;
    }

    public function flush()
    {
        $this->entityManager->persist($this->object);
        $this->entityManager->flush();
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @param QueryBuilder $queryBuilder
     */
    public function getKeyEntry(ServiceEntityRepository $repository, object $uploadLine)
    {
        if ( count( $this->keyFields ) <= 0 ) {
            return null;
        }

        $selection = array();

        foreach ( $this->keyFields as $keyField ) {
            $targetField = "";
            $targetValue = "";

            // find target field
            if ( isset( $this->map[$keyField] ) ) {
                $targetField = $this->map[$keyField];
            }

            // load value from uploaded data
            try {
                $targetValue = $this->accessor->getValue($uploadLine, $keyField);
            } catch (\Exception $e) {}

            if ( $targetField != "" && $targetValue != "" ) {
                $selection[lcfirst($targetField)] = $targetValue;
            }
        }

        return $repository->findOneBy( $selection );
    }
}
