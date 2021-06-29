<?php

namespace Hboie\DataIOBundle\Mapper;

use Doctrine\Persistence\ObjectManager;

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
     * @var array
     */
    private $date_format;

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

                foreach ($field->children() as $child) {
                    /** @var \SimpleXMLElement $child */
                    if ($child->getName() == 'validate') {
                        $child_attrib = $child->attributes();
                        if (isset($child_attrib['dateformat'])) {
                            $this->date_format[$field_name] = (string)$child_attrib['dateformat'];
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
                // use given dateformats to import date
                $target_value = null;

                if ( isset($this->date_format[$source_key]) ) {
                    foreach ( explode('|',$this->date_format[$source_key]) as $dateFormat ) {
                        $d = \DateTime::createFromFormat($dateFormat, $value);
                        if ( $d != null ) {
                            if ( $d->format( $dateFormat ) === $value ) {
                                $target_value = new \DateTime( $d->format('Y-m-d') );
                                break;
                            }
                        }
                    }
                }
            }

            if ( $target_value != null ) {
                $this->object->$setter_method($target_value);
            }
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
