<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Tests\Entity\TestEntity;
use Doctrine\ORM\EntityRepository;
use Hboie\DataIOBundle\Validation\DatabaseLookup;

class DataValidator2Test extends KernelTestCase
{
    /**
     * @var DataValidator
     */
    protected $dataValidator;

    protected function setUp()
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
     * @group dataio.validator
     */
    public function testNull()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::DATABASE_VAL,
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue'
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $this->dataValidator->validateObject($testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $this->dataValidator->validateObject($testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'The value "stringValue" should not be blank.');
    }

    /**
     * @group dataio.validator
     */
    public function testDatabase()
    {
        $this->dataValidator->addValidator('stringValue', [
            'type' => DataValidator::DATABASE_VAL,
            'nullable' => 'true',
            'entity' => 'TestEntity',
            'field' => 'testValue'
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('100');

        $errorList = $this->dataValidator->validateObject($testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(),
            'The value "stringValue" is not contained in field "testValue" in table "TestEntity".');

        $testObject->setStringValue('mockValue');

        $errorList = $this->dataValidator->validateObject($testObject);

        $this->assertTrue($errorList->isValid());
    }

}