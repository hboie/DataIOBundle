<?php

namespace Hboie\DataIOBundle\Mapper;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityMapper
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var PropertyAccess $accessor
     */
    protected $accessor;

    /**
     * @var array
     */
    private $colMap;

    /**
     * @var array
     */
    private $defaultValues;

    /**
     * @var array
     */
    private $mandatoryFields;

    /**
     * @var mixed
     */
    private $object;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->colMap = array();
        $this->defaultValues = array();
        $this->mandatoryFields = array();

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
            if(isset($fieldAttrib['name']))
            {
                $fieldName = (string)$fieldAttrib['name'];

                if(isset($fieldAttrib['mandatory'])) {
                    if($fieldAttrib['mandatory'] == 'true') {
                        array_push($this->mandatoryFields, $fieldName);
                    }
                }

                foreach($field->children() as $child) {
                    /** @var \SimpleXMLElement $child */
                    if($child->getName() == 'column') {
                        $columnAttrib = $child->attributes();
                        if(isset($columnAttrib['name'])) {
                            $columnName = (string)$columnAttrib['name'];
                            $this->colMap[strtolower($columnName)] = $fieldName;
                        }
                    } else if($child->getName() == 'default') {
                        $columnAttrib = $child->attributes();
                        if(isset($column_attrib['value'])) {
                            $defaultValue = (string)$columnAttrib['value'];
                            $this->defaultValues[$field_name] = $defaultValue;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $column_name
     * @param string $value
     * @return bool
     */
    public function insertValue($columnName, $value)
    {
        $key = strtolower($columnName);

        if(!isset($this->colMap[$key])) {
            return false;
        }

        $varName = $this->colMap[$key];

        try {
            $this->accessor->setValue($this->object, $varName, $value);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $row
     * @return bool
     */

    public function insertValues($row)
    {
        $valueInserted = false;
        foreach ($row as $colName => $value) {
            if($this->insertValue($colName, $value)) {
                $valueInserted = true;
            }
        }

        return $valueInserted;
    }

    /**
     * @return bool
     */

    public function insertDefaultValues()
    {
        $valuesInserted = true;
        foreach ( $this->defaultValues as $varName => $value ) {
            try {
                $this->accessor->setValue($this->object, $varName, $value);
            } catch (\Exception $e) {
                $valuesInserted = false;
            }
        }

        return $valuesInserted;
    }

    public function flush()
    {
        $this->entityManager->persist($this->object);
        $this->entityManager->flush();
    }

    /**
     * @param array $fields_array
     * @return bool
     */
    public function containsMandatoryFields($fieldsArray)
    {
        $diff = array_diff($this->mandatoryFields, $fieldsArray);

        if(count($diff) == 0) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

    /**
     * @return array
     */
    public function getMandatoryFields()
    {
        return $this->mandatoryFields;
    }

    /**
     * @return array
     */
    public function getMandatoryColumns()
    {
        $ret = array();
        foreach ($this->colMap as $colName => $fieldName) {
            if(in_array($fieldName, $this->mandatoryFields)) {
                $ret[] = $colName;
            }
        }
        
        return $ret;
    }

    /**
     * @param array $fields_array
     * @return bool
     */
    public function containsMandatoryColumns($columnsArray)
    {
        $diff = array_diff($this->getMandatoryColumns(), $columnsArray);

        if(count($diff) == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return array
     */
    public function getMap()
    {
        return $this->colMap;
    }

    /**
     * @return array
     */
    public  function getDefaultValues()
    {
        return $this->defaultValues;
    }
}