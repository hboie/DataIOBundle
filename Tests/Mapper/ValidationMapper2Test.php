<?php

namespace Hboie\DataIOBundle\Tests\Mapper;

use Hboie\DataIOBundle\Mapper\ValidationMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Tests\Entity\TestEntity;
use Doctrine\ORM\EntityRepository;
use Hboie\DataIOBundle\Validation\DatabaseLookup;

class ValidationMapper2Test extends KernelTestCase
{
    /**
     * @var DataValidator
     */
    protected $dataValidator;

    protected function setUp() : void
    {
        self::bootKernel();

        $frameworkValidator = static::$kernel->getContainer()->get('validator');

        $mockRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->any())
            ->method('findBy')
            ->will($this->returnCallback(function($params) {
                if ($params['testValue'] == 'mockValue') {
                    return $this->getMock(TestEntity::class);
                } else {
                    return null;
                }
            }));

        $mockEntityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($mockRepository));

        $databaseLookup = new DatabaseLookup($mockEntityManager);

        $validatorFactory = new DataFieldValidatorFactory($frameworkValidator, $mockEntityManager, $databaseLookup);

        $this->dataValidator = new DataValidator($validatorFactory);
    }
    
    /**
     * @group dataio.mapper
     */
    public function testBasic()
    {
        $validationMapper = new ValidationMapper($this->dataValidator);

        $validationMapper->loadXmlMapping(__DIR__.'/../Resources/config/test_doctrine.mapping.xml');

        $testObject = new DataFieldTestObject();
        $testObject->setStringValue('Das ist ein Test');
        $testObject->setDecimalValue('25.07');
        $testObject->setDateValue('2015-01-05');

        // test validation

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), 'ERROR: The value "StringValue" is not contained in field "testValue" in table "TestEntity".');
        $this->assertEquals($valResList->getJudge(), DataValidator::ERROR);

        $testObject->setStringValue('mockValue');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertTrue($valResList->isValid());

        $testObject->setDateValue('05.01.2015');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), 'WARNING: The value "DateValue" is not a valid date.');
        $this->assertEquals($valResList->getJudge(), DataValidator::WARNING);

        $testObject->setStringValue('not valid');

        $valResList = $validationMapper->validateObject($testObject);

        $errMsg = 'WARNING: The value "DateValue" is not a valid date, ';
        $errMsg .= 'ERROR: The value "StringValue" is not contained in field "testValue" in table "TestEntity".';
        $this->assertFalse($valResList->isValid());
        $this->assertEquals($valResList->getMessage(), $errMsg);
        $this->assertEquals($valResList->getJudge(), DataValidator::ERROR);
    }
}