<?php

namespace Hboie\DataIOBundle\Tests\Mapper;

use Hboie\DataIOBundle\Mapper\ValidationMapper;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Validation\DataValidator;
use Hboie\DataIOBundle\Validation\DataFieldValidatorFactory;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Tests\Entity\TestEntity;
use Hboie\DataIOBundle\Tests\Entity\TestMappingEntity;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Hboie\DataIOBundle\Validation\DatabaseLookup;

class ValidationMapper3Test extends KernelTestCase
{
    /**
     * @var DataValidator
     */
    protected $dataValidator;

    protected function setUp()
    {
        self::bootKernel();

        $frameworkValidator = static::$kernel->getContainer()->get('validator');

        // mock testObject

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

        $testMappingObject->setLookupField('mockLookupField');
        $testMappingObject->setLookupValue('mockLookupValue');
        $testMappingObject->setTargetField('mockTargetField');
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
                && $array[3] == 'StringValue';
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
        $mockLookupRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('m')
            ->will($this->returnValue($mockLookupQueryBuilder));

        // mock EntityManage
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

        $validatorFactory = new DataFieldValidatorFactory($frameworkValidator, $mockEntityManager, $databaseLookup);

        $this->dataValidator = new DataValidator($validatorFactory);
    }
    
    /**
     * @group dataio.mapper
     */
    public function testLookup()
    {
        $validationMapper = new ValidationMapper($this->dataValidator);

        $validationMapper->loadXmlMapping(__DIR__.'/../Resources/config/test_doctrine_lookup.mapping.xml');

        $testObject = new DataFieldTestObject();
        $testObject->setStringValue('mockValue');
        $testObject->setDecimalValue('25.07');
        $testObject->setDateValue('2015-01-05');
        $testObject->setMockLookup('lookupValue');

        // test validation
        $valResList = $validationMapper->validateObject($testObject);

        $this->assertTrue($valResList->isValid());
        $this->assertEquals($valResList->getJudge(), DataValidator::VALID);

        $testObject->setStringValue('');

        $valResList = $validationMapper->validateObject($testObject);

        $this->assertTrue($valResList->isValid());
        $this->assertEquals($valResList->getJudge(), DataValidator::VALID);
    }
}