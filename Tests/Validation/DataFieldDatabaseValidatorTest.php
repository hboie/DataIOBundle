<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Hboie\DataIOBundle\Validation\DataFieldValidator;
use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\ValidationResult;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataFieldDatabaseValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Hboie\DataIOBundle\Tests\Validation\DataFieldTestObject;
use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Tests\Entity\TestEntity;
use Hboie\DataIOBundle\Validation\DatabaseLookup;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Hboie\DataIOBundle\Tests\Entity\TestMappingEntity;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;


class DataFieldDatabaseValidatorTest extends KernelTestCase
{
    /**
     * @var ValidatorInterface
     */
    protected $frameworkValidator;

    protected function setUp() : void
    {
        self::bootKernel();

        $this->frameworkValidator = static::$kernel->getContainer()->get('validator');
    }
    
    /**
     * @group dataio.validator
     */
    public function testNullable()
    {
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

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createDatabaseValidator([
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue'
            ]);

        $testObject = new DataFieldTestObject();

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), 'This value should not be blank.');

        $databaseValidator->setNullable(true);
        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testDatabase()
    {
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

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createDatabaseValidator([
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue'
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('100');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(),
            'This value is not contained in field "testValue" in table "TestEntity".');

        $testObject->setStringValue('mockValue');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testFactory()
    {
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

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createValidator([
            'type' => DataValidator::DATABASE_VAL,
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue'
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('100');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(),
            'This value is not contained in field "testValue" in table "TestEntity".');

        $testObject->setStringValue('mockValue');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());
    }

    /**
     * @group dataio.validator
     */
    public function testSeverity()
    {
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

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createDatabaseValidator([
            'nullable' => false,
            'severity' => DataFieldValidator::ERROR
        ]);

        $testObject = new DataFieldTestObject();

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);

        $databaseValidator = $validatorFactory->createDatabaseValidator([
            'nullable' => false,
            'severity' => DataFieldValidator::WARNING
        ]);

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::ERROR);
        $this->assertEquals($errorList->getJudge(), DataFieldValidator::WARNING);
        $this->assertNotEquals($errorList->getJudge(), DataFieldValidator::INFO);
    }

    /**
     * @group dataio.validator
     */
    public function testLookup()
    {
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

        // mock mapping entity for lookup

        $testMappingObject = new TestMappingEntity();

        $result = 'mockValue';

        $testMappingObject->setLookupField('lookupField');
        $testMappingObject->setLookupValue('lookupValue');
        $testMappingObject->setTargetField('testValue');
        $testMappingObject->setTargetValue($result);

        // mock Query
        $mockLookupQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getOneOrNullResult', 'setMaxResults'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockLookupQuery->expects($this->any())
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnValue($mockLookupQuery));
        $mockLookupQuery->expects($this->any())
            ->method('getOneOrNullResult')
            ->will($this->returnValue($testMappingObject));

        // mock QueryBuilder
        $mockLookupQueryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLookupQueryBuilder->expects($this->at(0))
            ->method('where')
            ->with('m.lookupField = ?1')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(1))
            ->method('andWhere')
            ->with('m.lookupValue = ?2')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(2))
            ->method('andWhere')
            ->with('m.targetField = ?3')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(3))
            ->method('setParameters')
            ->with($this->callback(function($array){
                return $array[1] == 'mockLookup' && $array[2] == 'lookupValue'
                && $array[3] == 'stringValue';
            }))
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(4))
            ->method('getQuery')
            ->will($this->returnValue($mockLookupQuery));

        // mock EntityRepository
        $mockLookupRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLookupRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('m')
            ->will($this->returnValue($mockLookupQueryBuilder));

        // mock EntityManager
        $mockLookupEntityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // register repository
        $mockLookupEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('TestMappingEntity')
            ->will($this->returnValue($mockLookupRepository));

        $databaseLookup = new DatabaseLookup($mockLookupEntityManager);
        $databaseLookup->setLookupAttributes([
            'lookup-entity' => 'TestMappingEntity',
            'lookup-prefix' => 'lookup',
            'target-prefix' => 'target'
        ]);

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createValidator([
            'type' => DataValidator::DATABASE_VAL,
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue',
            'lookup-field' => 'mockLookup'
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('mockValue');
        $testObject->setMockLookup('lookupValue');
        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $msg = 'This value should not be blank, ';
        $msg .= 'replaced by value "mockValue" from field "mockLookup" from lookup-table.';
        $this->assertTrue($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), $msg);
        $this->assertEquals($errorList->getJudge(), ValidationResult::VALID);
    }

    /**
     * @group dataio.validator
     */
    public function testPending()
    {
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

        // mock mapping entity for lookup

        // mock Query
        $mockLookupQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getOneOrNullResult', 'setMaxResults'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockLookupQuery->expects($this->any())
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnValue($mockLookupQuery));
        $mockLookupQuery->expects($this->any())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(null));

        // mock QueryBuilder
        $mockLookupQueryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLookupQueryBuilder->expects($this->at(0))
            ->method('where')
            ->with('m.lookupField = ?1')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(1))
            ->method('andWhere')
            ->with('m.lookupValue = ?2')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(2))
            ->method('andWhere')
            ->with('m.targetField = ?3')
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(3))
            ->method('setParameters')
            ->with($this->callback(function($array){
                return $array[1] == 'mockLookup' && $array[2] == 'lookupValue'
                && $array[3] == 'stringValue';
            }))
            ->will($this->returnValue($mockLookupQueryBuilder));
        $mockLookupQueryBuilder->expects($this->at(4))
            ->method('getQuery')
            ->will($this->returnValue($mockLookupQuery));

        // mock EntityRepository
        $mockLookupRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLookupRepository->expects($this->any())
            ->method('createQueryBuilder')
            ->with('m')
            ->will($this->returnValue($mockLookupQueryBuilder));

        // mock EntityManager
        $mockLookupEntityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        // register repository
        $mockLookupEntityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('TestMappingEntity')
            ->will($this->returnValue($mockLookupRepository));

        $databaseLookup = new DatabaseLookup($mockLookupEntityManager);
        $databaseLookup->setLookupAttributes([
            'lookup-entity' => 'TestMappingEntity',
            'lookup-prefix' => 'lookup',
            'target-prefix' => 'target'
        ]);

        $validatorFactory = new DataFieldValidatorFactory($this->frameworkValidator, $mockEntityManager, $databaseLookup);

        $databaseValidator = $validatorFactory->createValidator([
            'type' => DataValidator::DATABASE_VAL,
            'nullable' => false,
            'entity' => 'TestEntity',
            'field' => 'testValue',
            'lookup-field' => 'mockLookup'
        ]);

        $testObject = new DataFieldTestObject();

        $testObject->setStringValue('mockValue');
        $testObject->setMockLookup('lookupValue');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $this->assertTrue($errorList->isValid());

        $testObject->setStringValue('');

        $errorList = $databaseValidator->validate('stringValue', $testObject);

        $msg = 'This value should not be blank, ';
        $msg .= 'the value "lookupValue" cannot be found in field "mockLookup" in lookup-table.';
        $this->assertFalse($errorList->isValid());
        $this->assertEquals($errorList->getMessage(), $msg);
        $this->assertEquals($errorList->getJudge(), ValidationResult::PENDING);

        $mapping = $databaseLookup->getPendingMapping();
        $pending = array_pop($mapping);

        $this->assertEquals($pending['lookupField'], 'mockLookup');
        $this->assertEquals($pending['lookupValue'], 'lookupValue');
        $this->assertEquals($pending['targetField'], 'stringValue');

    }

}