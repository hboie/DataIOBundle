<?php

namespace Hboie\DataIOBundle\Mapper;

use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Hboie\DataIOBundle\Validation\ValidationResultList;

class LookupMapper
{
    /**
     * @var array
     */
    public $lookupFields;

    /**
     * LookupMapper constructor.
     */
    public function __construct()
    {
        $this->lookupFields = array();
    }

    /**
     * @param string $filename
     */
    public function loadXmlMapping($filename)
    {
        $xml = new \SimpleXmlElement(file_get_contents($filename));

        if( $xml->getName() == 'mapping' ) {

            foreach ($xml->field as $field) {
                $fieldAttrib = $field->attributes();
                if ( isset($fieldAttrib['name']) ) {
                    $parName = (string)$fieldAttrib['name'];
                    foreach ($field->children() as $child) {
                        /** @var \SimpleXMLElement $child */
                        if ($child->getName() == 'validate') {
                            $this->addLookup($parName, $child);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $parName
     * @param \SimpleXMLElement $child
     */
    public function addLookup($parName, $child)
    {
        $childAttrib = $child->attributes();

        if (isset($childAttrib['lookup-field'])) {
            $this->lookupFields[$parName] = (string)$childAttrib['lookup-field'];
        }
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getLookupField($key)
    {
        if (isset($this->lookupFields[$key])) {
            return $this->lookupFields[$key];
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        $ret_array = array();
        
        foreach ($this->lookupFields as $key => $lookupField) {
            $ret_array[] = $key;
        }
        
        return $ret_array;
    }
}
