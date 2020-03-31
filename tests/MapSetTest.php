<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */
declare( strict_types=1 );

require_once( __DIR__ . '/SampleMapSet.php' );
require_once( __DIR__ . '/SetTest.php' );

use buffalokiwi\buffalotools\types\IBitSet;


/**
 * Tests the MapSet class 
 */
class MapSetTest extends SetTest
{
  
  /**
   * Create the IBitSet instance
   * @return IBitSet instance to test 
   */
  protected function createIBitSetInstance() : IBitSet
  {
    return new SampleMapSet();
  }  
  
  
  /**
   * Tests the constructor.
   * Expects the constructor to accept a map of members to value and that
   * the bits are set and the values can be retrieved.
   * @return void
   */
  public function testConstructor() : void
  {
    $c = new SampleMapSet();
    $this->assertEquals( 0, $c->getValue());
    
    $c = new SampleMapSet( [SampleMapSet::KEY1 => 'test'] );
    $this->assertTrue( $c->hasVal( SampleMapSet::KEY1 ));
    
    $this->assertEquals( 'test', $c->get( SampleMapSet::KEY1 ));
    
    $this->expectException( TypeError::class );
    $c = new SampleMapSet( null );
  }
  
  
  public function testGetAddMapSilentlyFailsWithInvalidValue() : void
  {
    //..This should silently fail
    $this->set->addMap( 'Invalid', 'invalid' );    
  }
  
  
  /**
   * Tests the get() and addMap() methods 
   * @return void
   */
  public function testGetSetMapValue() : void
  {
    $this->set->clear();
    $this->assertTrue( $this->set->isEmpty());
    
    $this->assertEmpty( $this->set->get( SampleMapSet::KEY1 ));
    
    $this->set->addMap( SampleMapSet::KEY1, 'test' );
    $this->assertTrue( $this->set->hasVal( SampleMapSet::KEY1 ));
    
    $this->assertEquals( 'test', $this->set->get( SampleMapSet::KEY1 ));
    
    $this->expectException( TypeError::class );
    $this->set->addMap( null, null );
  }
  
  
  public function testMapDump() : void
  {
    $this->set->clear();
    $this->assertTrue( $this->set->isEmpty());
    $this->set->addMap( SampleMapSet::KEY1, 'test' );
    $this->set->addMap( SampleMapSet::KEY2, 'test2' );
    
    $arr = $this->set->map();
    
    $this->assertIsArray( $arr );
    $this->assertEquals( 2, sizeof( $arr ));
    
    $keys = array_keys( $arr );
    $vals = array_values( $arr );
    
    $this->assertEquals( SampleMapSet::KEY1, reset( $keys ));
    $this->assertEquals( SampleMapSet::KEY2, end( $keys ));

    $this->assertEquals( 'test', reset( $vals ));
    $this->assertEquals( 'test2', end( $vals ));
  }
}
