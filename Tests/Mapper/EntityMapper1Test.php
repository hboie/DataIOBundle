<?php

namespace Hboie\DataIOBundle\Tests\Mapper;

use Hboie\DataIOBundle\Mapper\EntityMapper;
use Hboie\DataIOBundle\Mapper\ValidationMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;
use Doctrine\Common\Persistence\ObjectManager;

class EntityMapper1Test extends KernelTestCase
{
    protected function setUp()
    {
        self::bootKernel();
    }
    
    /**
     * @group dataio.mapper
     */
    public function testBasic()
    {
        $mockEntityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityMapper = new EntityMapper($mockEntityManager);

        $entityMapper->loadXmlMapping(__DIR__.'/../Resources/config/test_basic.mapping.xml');

        // test mapping
        $map = $entityMapper->getMap();

        $this->assertEquals($map['date'], 'DateValue');
        $this->assertEquals($map['decimal'], 'DecimalValue');
        $this->assertEquals($map['string'], 'StringValue');

        $testObject = new DataFieldTestObject();
        $entityMapper->setObject($testObject);

        $this->assertEquals($testObject->getStringValue(), '');

        $entityMapper->insertDefaultValues();
        $this->assertEquals($testObject->getStringValue(), 'DefaultValue');
    }
}