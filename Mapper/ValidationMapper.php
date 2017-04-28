<?php

namespace Hboie\DataIOBundle\Mapper;

use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Hboie\DataIOBundle\Validation\ValidationResultList;

class ValidationMapper
{
    /**
     * @var DataValidator
     */
    private $dataValidator;

    public function __construct(DataValidator $dataValidator)
    {
        $this->dataValidator = $dataValidator;
    }

    /**
     * @param string $filename
     */
    public function loadXmlMapping($filename)
    {
        $xml = new \SimpleXmlElement(file_get_contents($filename));

        if( $xml->getName() == 'mapping' ) {
            $this->dataValidator->getDatabaseLookup()->setLookupAttributes($xml->attributes());
        }

        foreach ($xml->field as $field) {
            $fieldAttrib = $field->attributes();
            if ( isset($fieldAttrib['name']) ) {
                $parName = (string)$fieldAttrib['name'];
                foreach ($field->children() as $child) {
                    /** @var \SimpleXMLElement $child */
                    if ($child->getName() == 'validate') {
                        $this->addValidation($parName, $child);
                    }
                }
            }
        }
    }

    /**
     * read given params from node
     *
     * @param \SimpleXMLElement $child
     * @param array $params
     * @return array
     */
    public function getParams($child, $params)
    {
        $childAttrib = $child->attributes();

        $retArray = array();
        foreach ($params as $param) {
            if (isset($childAttrib[$param])) {
                $retArray[$param] = (string)$childAttrib[$param];
            }
        }

        return $retArray;
    }

    /**
     * @param string $parName
     * @param \SimpleXMLElement $child
     */
    public function addValidation($parName, $child)
    {
        $paramArray = $this->getParams($child, ['type']);

        if ( isset($paramArray['type']) ) {
            switch( strtolower($paramArray['type']) ) {
                case DataValidator::STRING_VAL:
                    $params = $this->getParams($child, ['severity', 'nullable', 'length']);
                    $this->dataValidator->addValidator($parName,
                        array_merge(['type' => DataValidator::STRING_VAL], $params) );
                    break;
                case DataValidator::DATE_VAL:
                    $params = $this->getParams($child, ['severity', 'nullable']);
                    $this->dataValidator->addValidator($parName,
                        array_merge(['type' => DataValidator::DATE_VAL], $params) );
                    break;
                case DataValidator::DECIMAL_VAL:
                    $params = $this->getParams($child, ['severity', 'nullable', 'precision', 'scale']);
                    $this->dataValidator->addValidator($parName,
                        array_merge(['type' => DataValidator::DECIMAL_VAL], $params) );
                    break;
                case DataValidator::DATABASE_VAL:
                    $params = $this->getParams($child, ['severity', 'nullable', 'entity', 'field', 'lookup-field']);
                    $this->dataValidator->addValidator($parName,
                        array_merge(['type' => DataValidator::DATABASE_VAL], $params) );
                    break;
            }
        }
    }
    
    /**
     * @param mixed $object
     * @return ValidationResultList
     */
    public function validateObject($object)
    {
        return $this->dataValidator->validateObject($object);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return ValidationResult
     */
    public function validateValue($key, $value)
    {
        return $this->dataValidator->validateValue($key, $value);
    }
    
    /**
     * @return array
     */
    public function getPendingMapping()
    {
        return $this->dataValidator->getDatabaseLookup()->getPendingMapping();
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function addLookupCondition($field, $value)
    {
        $this->dataValidator->getDatabaseLookup()->addCondition($field, $value);
    }
}
