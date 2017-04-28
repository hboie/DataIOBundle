<?php

namespace Hboie\DataIOBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TestEntity
 *
 * @ORM\Table
 * @ORM\Entity
 */
class TestMappingEntity
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="LookupField", type="string", length=25, nullable=true)
     */
    private $lookupField;

    /**
     * @var string
     *
     * @ORM\Column(name="LookupValue", type="string", length=25, nullable=true)
     */
    private $lookupValue;


    /**
     * @var string
     *
     * @ORM\Column(name="TargetField", type="string", length=25, nullable=true)
     */
    private $targetField;

    /**
     * @var string
     *
     * @ORM\Column(name="TargetValue", type="string", length=25, nullable=true)
     */
    private $targetValue;

    /**
     * @return string
     */
    public function getLookupField()
    {
        return $this->lookupField;
    }

    /**
     * @param string $lookupField
     */
    public function setLookupField($lookupField)
    {
        $this->lookupField = $lookupField;
    }

    /**
     * @return string
     */
    public function getLookupValue()
    {
        return $this->lookupValue;
    }

    /**
     * @param string $lookupValue
     */
    public function setLookupValue($lookupValue)
    {
        $this->lookupValue = $lookupValue;
    }

    /**
     * @return string
     */
    public function getTargetField()
    {
        return $this->targetField;
    }

    /**
     * @param string $targetField
     */
    public function setTargetField($targetField)
    {
        $this->targetField = $targetField;
    }

    /**
     * @return string
     */
    public function getTargetValue()
    {
        return $this->targetValue;
    }

    /**
     * @param string $targetValue
     */
    public function setTargetValue($targetValue)
    {
        $this->targetValue = $targetValue;
    }

}
