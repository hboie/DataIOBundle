<?php

namespace Hboie\DataIOBundle\Tests\Validation;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use Doctrine\ORM\EntityRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Hboie\DataIOBundle\Validation\DatabaseLookup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Hboie\DataIOBundle\Tests\Entity\TestMappingEntity;


class DatabaseLookupTest extends KernelTestCase
{
    /**
     * @var ObjectManager
     */
    protected $entityManager;

    protected function setUp() : void
    {
        // mock EntityManager
        $this->entityManager = $this
            ->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }
    
    /**
     * @group dataio.validator
     */
    public function testLookup1()
    {
        $testObject = new TestMappingEntity();

        $result = 'mockTargetValue';

        $testObject->setLookupField('mockLookupField');
        $testObject->setLookupValue('mockLookupValue');
        $testObject->setTargetField('mockTargetField');
        $testObject->setTargetValue($result);

        // mock Query
        $mockQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getOneOrNullResult', 'setMaxResults'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockQuery->expects($this->any())
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->any())
            ->method('getOneOrNullResult')
            ->will($this->returnValue($testObject));

        // mock QueryBuilder
        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockQueryBuilder->expects($this->at(0))
            ->method('where')
            ->with('m.lookupField = ?1')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(1))
            ->method('andWhere')
            ->with('m.lookupValue = ?2')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(2))
            ->method('andWhere')
            ->with('m.targetField = ?3')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(3))
            ->method('setParameters')
            ->with($this->callback(function($array){
                return $array[1] == 'mockLookupField' && $array[2] == 'mockLookupValue'
                && $array[3] == 'mockTargetField';
            }))
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(4))
            ->method('getQuery')
            ->will($this->returnValue($mockQuery));

        // mock EntityRepository
        $mockRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('m')
            ->will($this->returnValue($mockQueryBuilder));

        // register repository
        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('TestMappingEntity')
            ->will($this->returnValue($mockRepository));

        $databaseLookup = new DatabaseLookup($this->entityManager);
        $databaseLookup->setLookupAttributes([
            'lookup-entity' => 'TestMappingEntity',
            'lookup-prefix' => 'lookup',
            'target-prefix' => 'target'
        ]);

        $this->assertEquals($result, $databaseLookup->lookup('mockTargetField', 'mockLookupField', 'mockLookupValue'));
    }

    /**
     * @group dataio.validator
     */
    public function testLookup2()
    {
        $testObject = new TestMappingEntity();

        // mock Query
        $mockQuery = $this->getMockBuilder(AbstractQuery::class)
            ->setMethods(array('getOneOrNullResult', 'setMaxResults'))
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $mockQuery->expects($this->any())
            ->method('setMaxResults')
            ->with(1)
            ->will($this->returnValue($mockQuery));
        $mockQuery->expects($this->any())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(null));

        // mock QueryBuilder
        $mockQueryBuilder = $this->getMockBuilder(QueryBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockQueryBuilder->expects($this->at(0))
            ->method('where')
            ->with('m.lookupField = ?1')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(1))
            ->method('andWhere')
            ->with('m.lookupValue = ?2')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(2))
            ->method('andWhere')
            ->with('m.targetField = ?3')
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(3))
            ->method('setParameters')
            ->with($this->callback(function($array){
                return $array[1] == 'mockLookupField' && $array[2] == 'mockLookupValue'
                    && $array[3] == 'mockTargetField';
            }))
            ->will($this->returnValue($mockQueryBuilder));
        $mockQueryBuilder->expects($this->at(4))
            ->method('getQuery')
            ->will($this->returnValue($mockQuery));

        // mock EntityRepository
        $mockRepository = $this
            ->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockRepository->expects($this->once())
            ->method('createQueryBuilder')
            ->with('m')
            ->will($this->returnValue($mockQueryBuilder));

        // register repository
        $this->entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->with('TestMappingEntity')
            ->will($this->returnValue($mockRepository));

        $databaseLookup = new DatabaseLookup($this->entityManager);
        $databaseLookup->setLookupAttributes([
            'lookup-entity' => 'TestMappingEntity',
            'lookup-prefix' => 'lookup',
            'target-prefix' => 'target'
        ]);

        $this->assertEquals('', $databaseLookup->lookup('mockTargetField', 'mockLookupField', 'mockLookupValue'));

        $mapping = $databaseLookup->getPendingMapping();
        $pending = array_pop($mapping);

        $this->assertEquals($pending['lookupField'], 'mockLookupField');
        $this->assertEquals($pending['lookupValue'], 'mockLookupValue');
        $this->assertEquals($pending['targetField'], 'mockTargetField');
    }
}