<?php

namespace Hboie\DataIOBundle\Mapper;

use Doctrine\Common\Persistence\ObjectManager;

class ImportMapper
{
    /**
     * @var ObjectManager
     */
    private $entity_manager;

    /**
     * @var array
     */
    private $map;

    /**
     * @var array
     */
    private $format;

    /**
     * @var mixed
     */
    private $object;

    public function __construct(ObjectManager $entity_manager)
    {
        $this->entity_manager = $entity_manager;
        $this->map = array();
        $this->format = array();
    }

    /**
     * @param string $filename
     */
    public function loadXmlMapping($filename)
    {
        $xml = new \SimpleXmlElement(file_get_contents($filename));
        foreach ($xml->field as $field) {
            $field_attrib = $field->attributes();

            $notarget = false;
            if (isset($field_attrib['notarget'])) {
                $notarget = (string)$field_attrib['notarget'];
                if (strtolower($notarget) == 'true') {
                    $notarget = true;
                }
            }

            $field_name = "";
            if (isset($field_attrib['name'])) {
                $field_name = (string)$field_attrib['name'];
            }

            $target = "";
            if (isset($field_attrib['target'])) {
                $target = (string)$field_attrib['target'];
            }

            $format = "";
            if (isset($field_attrib['format'])) {
                $format = (string)$field_attrib['format'];
            }

            if (!$notarget && $field_name != "") {
                if ($target == "") {
                    $target = $field_name;
                }
                $this->map[$field_name] = $target;
                $this->format[$field_name] = $format;
            }
        }
    }

    /**
     * @param mixed $source
     * @return bool
     */
    public function insertValues($source)
    {
        foreach ($this->map as $source_key => $target_key) {
            $getter_method = 'get' . $source_key;
            $setter_method = 'set' . $target_key;
            if (!method_exists($source, $getter_method)) {
                return false;
            }

            if (!method_exists($this->object, $setter_method)) {
                return false;
            }

            $value = $source->$getter_method();
            $target_value = $value;
            if($this->format[$source_key] == 'date') {
                $target_value = new \DateTime($value);
            }
            $this->object->$setter_method($target_value);
        }
        return true;
    }

    public function flush()
    {
        $this->entity_manager->persist($this->object);
        $this->entity_manager->flush();
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
    }

}
