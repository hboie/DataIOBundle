<?php

namespace Hboie\DataIOBundle\Tests\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TestEntity
 *
 * @ORM\Table
 * @ORM\Entity
 */
class TestEntity
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
     * @ORM\Column(name="TestValue", type="string", length=25, nullable=true)
     */
    private $testValue;

    /**
     * @var string
     *
     * @ORM\Column(name="StringValue", type="string", length=25, nullable=true)
     */
    private $stringValue;

    /**
     * @var double
     *
     * @ORM\Column(name="DecimalValue", type="string", precision=9, scale=2, nullable=true)
     */
    private $decimalValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DateValue", type="datetime", nullable=true)
     */
    private $dateValue;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTestValue()
    {
        return $this->testValue;
    }

    /**
     * @param string $TestValue
     */
    public function setTestValue($TestValue)
    {
        $this->testValue = $TestValue;
    }

    /**
     * @return string
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param string $stringValue
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
    }

    /**
     * @return float
     */
    public function getDecimalValue()
    {
        return $this->decimalValue;
    }

    /**
     * @param float $decimalValue
     */
    public function setDecimalValue($decimalValue)
    {
        $this->decimalValue = $decimalValue;
    }

    /**
     * @return \DateTime
     */
    public function getDateValue()
    {
        return $this->dateValue;
    }

    /**
     * @param \DateTime $dateValue
     */
    public function setDateValue($dateValue)
    {
        $this->dateValue = $dateValue;
    }
}
