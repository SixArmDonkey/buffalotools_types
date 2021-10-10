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

use buffalokiwi\buffalotools\types\IBitSet;

require_once( __DIR__ . '/SampleSet.php' );
require_once( __DIR__ . '/SampleSetConst.php' );


class SetTest extends AbstractSetTest
{
  
  /**
   * Create the IBitSet instance
   * @return IBitSet instance to test 
   */
  protected function createIBitSetInstance() : IBitSet
  {
    return new SampleSet();
  }
  
  
  
  
  
  
  /**
   * Tests the constructor.
   * 
   * Set constructors must accept:
   * 1) multiple string arguments 
   * 2) multiple arguments as a single string containing a comma-delimited list of member names
   * 
   * Member names supplied to the constructor must have their corresponding bits set 
   * 
   */
  public function testConstructor() : void
  {
    $set = new SampleSet();
    $this->assertEquals( 0, $set->getValue());
    $this->assertFalse( $set->hasVal( SampleSet::KEY1 ));

    
    $set = new SampleSet( SampleSet::KEY1, SampleSet::KEY2, SampleSet::KEY3 );
    $this->assertTrue( $set->hasVal( SampleSet::KEY1 ));
    $this->assertTrue( $set->hasVal( SampleSet::KEY2 ));
    $this->assertTrue( $set->hasVal( SampleSet::KEY3 ));
    
    $arg = implode( ',', [SampleSet::KEY1, SampleSet::KEY3] );
    $set = new SampleSet( $arg );
    $this->assertTrue( $set->hasVal( SampleSet::KEY1 ));
    $this->assertFalse( $set->hasVal( SampleSet::KEY2 ));
    $this->assertTrue( $set->hasVal( SampleSet::KEY3 ));
  }
  
  
  /**
   * Tests the equals() method.
   * ISet::equals() expects both sets to be of the same class and have equal active members.
   * 
   * @return void
   */
  public function testEquals() : void
  {
    $set1 = new SampleSet( SampleSet::KEY1 );
    $set2 = new SampleSet( SampleSet::KEY1 );
    $set3 = new SampleSet( SampleSet::KEY2 );
    
    $this->assertTrue( $set1->equals( $set2 ));
    $this->assertFalse( $set1->equals( $set3 ));
    $this->expectException( TypeError::class );
    $set1->equals( null );
  }
  
  
  /**
   * Tests the __set() magic method.
   * Supplied property name to set may be a constant from the class or the member name string.
   * Supplied value must be a boolean
   * Expects the corresponding bit to be set or unset by calling add() and remove()
   */
  public function testMagicSet() : void
  {
    $this->set->clear();
    
    $this->set->value1 = true;
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    
    $this->set->value1 = false;
    $this->assertFalse( $this->set->hasVal( SampleSet::KEY1 ));
    
    $this->set->KEY1 = true;
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    
    $this->set->KEY1 = false;
    $this->assertFalse( $this->set->hasVal( SampleSet::KEY1 ));    
  }
  
  
  /**
   * Tests the __get magic method.
   * Supplied property name to set may be a constant from the class or the member name string.
   * Expected return value is the integer value of the member 
   */
  public function testMagicGet() : void
  {
    $this->assertGreaterThan( 0, $this->set->value1 );
    $this->assertGreaterThan( 0, $this->set->KEY1 );
    
    $this->expectException( InvalidArgumentException::class );
    $this->set->InvalidKey;
  }
  
  
  /**
   * Tests the __isset magic method.
   * Expects isset( member name or constant ) to equal true for active members 
   */
  public function testMagicIsset() : void
  {
    $this->set->clear();
    $this->set->add( SampleSet::KEY1 );
    
    $this->assertTrue( isset( $this->set->value1 ));
    $this->assertTrue( isset( $this->set->KEY1 ));
    $this->assertFalse( isset( $this->set->KEY2 ));
    
    $this->expectException( InvalidArgumentException::class );
    isset( $this->set->InvalidKey );
  }
  
  
  /**
   * Tests the __unset magic method
   * Expects unset( member name or constant ) to unset any corresponding bits 
   * @return void
   */
  public function testMagicUnset() : void
  {
    $this->set->clear();
    $this->set->add( SampleSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    
    unset( $this->set->value1 );
    $this->assertFalse( $this->set->hasVal( SampleSet::KEY1 ));
    
    $this->set->add( SampleSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    
    unset( $this->set->KEY1 );
    $this->assertFalse( $this->set->hasVal( SampleSet::KEY1 ));
    
    //..No error expected 
    unset( $this->set->KEY1 );
    
    //..No error expected
    unset( $this->set->InvalidKey );
    
  }
  
  
  /**
   * Tests the __call magic method.
   * Expects supplied method name to be a constant or member string and the result
   * of that method should be true or false depending on if the corresponding bit is set.
   * ie: $set->MEMBER() == true or $set->stringvalue() == true 
   */
  public function testMagicCall() : void
  {
    $this->set->clear();
    $this->set->add( SampleSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    
    $this->assertTrue( $this->set->KEY1());
    $this->assertTrue( $this->set->value1());
    $this->assertFalse( $this->set->KEY2());
    
    $this->assertFalse( $this->set->Invalid());
  }
  
  
  /**
   * Tests the __toString() magic method.
   * Expects a comma-delimited list of active members.
   * @return void
   */
  public function testMagicToString() : void
  {
    $this->set->clear();
    $this->set->add( SampleSet::KEY1, SampleSet::KEY2 );
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY1 ));
    $this->assertTrue( $this->set->hasVal( SampleSet::KEY2 ));
    
    $this->assertEquals( implode( ',', [SampleSet::KEY1, SampleSet::KEY2] ), (string)$this->set );
  }
  
  
  public function testConstInit() : void
  {
    $set = new SampleSetConst();
    
    $this->assertEquals( 3, sizeof( $set->getMembers()));
    $this->assertTrue( $set->isMember( 'value1', 'value2', 'value3' ));
  }  
}
