<?php

namespace Hboie\DataIOBundle\Mapper;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityMapper
{
    /**
     * @var ObjectManager
     */
    private $entity_manager;

    /**
     * @var PropertyAccess $accessor
     */
    protected $accessor;

    /**
     * @var array
     */
    private $col_map;

    /**
     * @var array
     */
    private $mandatory_fields;

    /**
     * @var mixed
     */
    private $object;

    public function __construct(ObjectManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        $this->col_map = array();
        $this->mandatory_fields = array();

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * @param string $filename
     */
    public function loadXmlMapping($filename)
    {
        $xml = new \SimpleXmlElement(file_get_contents($filename));
        foreach ($xml->field as $field) {
            $field_attrib = $field->attributes();
            if(isset($field_attrib['name']))
            {
                $field_name = (string)$field_attrib['name'];

                if(isset($field_attrib['mandatory'])) {
                    if($field_attrib['mandatory'] == 'true') {
                        array_push($this->mandatory_fields, $field_name);
                    }
                }

                foreach($field->children() as $child) {
                    /** @var \SimpleXMLElement $child */
                    if($child->getName() == 'column') {
                        $column_attrib = $child->attributes();
                        if(isset($column_attrib['name'])) {
                            $column_name = (string)$column_attrib['name'];
                            $this->col_map[strtolower($column_name)] = $field_name;
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
    public function insertValue($column_name, $value)
    {
        $key = strtolower($column_name);

        if(!isset($this->col_map[$key])) {
            return false;
        }

        $var_name = $this->col_map[$key];

        try {
            $this->accessor->setValue($this->object, $key, $value);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    public function insertValues($row)
    {
        $value_inserted = false;
        foreach ($row as $col_name => $value) {
            if($this->insertValue($col_name, $value)) {
                $value_inserted = true;
            }
        }

        return $value_inserted;
    }

    public function flush()
    {
        $this->entity_manager->persist($this->object);
        $this->entity_manager->flush();
    }

    /**
     * @param array $fields_array
     * @return bool
     */
    public function containsMandatoryFields($fields_array)
    {
        $diff = array_diff($this->mandatory_fields, $fields_array);

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
        return $this->mandatory_fields;
    }

    /**
     * @return array
     */
    public function getMandatoryColumns()
    {
        $ret = array();
        foreach ($this->col_map as $col_name => $field_name) {
            if(in_array($field_name, $this->mandatory_fields)) {
                $ret[] = $col_name;
            }
        }
        
        return $ret;
    }

    /**
     * @param array $fields_array
     * @return bool
     */
    public function containsMandatoryColumns($columns_array)
    {
        $diff = array_diff($this->getMandatoryColumns(), $columns_array);

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
        return $this->col_map;
    }
}