<?php

namespace Hboie\DataIOBundle\Tests\Import;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Hboie\DataIOBundle\Import\CSVLoader;

class CSVLoaderTest extends KernelTestCase
{
    protected function setUp()
    {
        self::bootKernel();
    }
    
    /**
     * @group dataio.import
     */
    public function testLoader()
    {
        $csvLoader = new CSVLoader();
        $csvLoader->openFile(__DIR__.'/../Import/test1.csv');

        $this->assertEquals($csvLoader->getHighestRow(), 5);
        $this->assertEquals($csvLoader->getHighestColumn(), 4);

        $this->assertTrue($csvLoader->isValidColName("Wert2"));
        $this->assertFalse($csvLoader->isValidColName("KeinWert"));

        $this->assertTrue( $csvLoader->valid() );
        $row = $csvLoader->getRow();
        $this->assertTrue( isset($row[strtolower('Wert1')]) );
        if ( isset($row[strtolower('Wert1')]) ) {
            $this->assertEquals($row[strtolower('Wert1')], 0);
        }

        $this->assertTrue( isset($row[strtolower('Wert2')]) );
        if ( isset($row[strtolower('Wert2')]) ) {
            $this->assertEquals($row[strtolower('Wert2')], "Test1");
        }

        $this->assertTrue( $csvLoader->next() );
        $this->assertTrue( $csvLoader->valid() );
        $row = $csvLoader->getRow();
        $this->assertTrue( isset($row[strtolower('Wert3')]) );
        if ( isset($row[strtolower('Wert3')]) ) {
            $this->assertEquals($row[strtolower('Wert3')], "Das ist ein zweiter Test");
        }

        $this->assertTrue( isset($row[strtolower('Wert4')]) );
        if ( isset($row[strtolower('Wert4')]) ) {
            $this->assertEquals($row[strtolower('Wert4')], 124.65);
        }

        $this->assertTrue( $csvLoader->next() );
        $this->assertTrue( $csvLoader->valid() );
        $row = $csvLoader->getRow();
        $this->assertTrue( isset($row[strtolower('Wert1')]) );
        if ( isset($row[strtolower('Wert1')]) ) {
            $this->assertEquals($row[strtolower('Wert1')], 2);
        }

        $this->assertTrue( isset($row[strtolower('Wert2')]) );
        if ( isset($row[strtolower('Wert2')]) ) {
            $this->assertEquals($row[strtolower('Wert2')], "Test3");
        }

        $this->assertTrue( $csvLoader->next() );
        $this->assertTrue( $csvLoader->valid() );
        $this->assertTrue( $csvLoader->next() );
        $this->assertTrue( $csvLoader->valid() );
        $row = $csvLoader->getRow();
        $this->assertTrue( isset($row[strtolower('Wert3')]) );
        if ( isset($row[strtolower('Wert3')]) ) {
            $this->assertEquals($row[strtolower('Wert3')], "Das ist ein fünfter Test");
        }
        $this->assertEquals($csvLoader->getCell('Wert3'), "Das ist ein fünfter Test");

        $this->assertTrue( isset($row[strtolower('Wert4')]) );
        if ( isset($row[strtolower('Wert4')]) ) {
            $this->assertEquals($row[strtolower('Wert4')], 0.3);
        }
        $this->assertEquals($csvLoader->getCell('Wert4'), 0.3);

        $this->assertFalse( $csvLoader->next() );
        $this->assertFalse( $csvLoader->valid() );
    }
}