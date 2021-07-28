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


require_once( __DIR__ . '/AbstractBigSetTest.php' );
require_once( __DIR__ . '/SampleBigSet.php' );

use buffalokiwi\buffalotools\types\IBigSet;


class BigSetTest extends AbstractBigSetTest
{  
  /**
   * Create the IBitSet instance
   * @return IBitSet instance to test 
   */
  protected function createIBigSetInstance() : IBigSet
  {
    return new SampleBigSet();
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
    $set = new SampleBigSet();    
    $this->assertEmpty( $set->getActiveMembers());
    $this->assertFalse( $set->hasVal( SampleBigSet::KEY1 ));

    
    $set = new SampleBigSet( SampleBigSet::KEY1, SampleBigSet::KEY2, SampleBigSet::KEY3 );
    $this->assertTrue( $set->hasVal( SampleBigSet::KEY1 ));
    $this->assertTrue( $set->hasVal( SampleBigSet::KEY2 ));
    $this->assertTrue( $set->hasVal( SampleBigSet::KEY3 ));
    
    $arg = implode( ',', [SampleBigSet::KEY1, SampleBigSet::KEY3] );
    $set = new SampleBigSet( $arg );
    $this->assertTrue( $set->hasVal( SampleBigSet::KEY1 ));
    $this->assertFalse( $set->hasVal( SampleBigSet::KEY2 ));
    $this->assertTrue( $set->hasVal( SampleBigSet::KEY3 ));
  }
  
  
  /**
   * Tests the equals() method.
   * ISet::equals() expects both sets to be of the same class and have equal active members.
   * 
   * @return void
   */
  public function testEquals() : void
  {
    $set1 = new SampleBigSet( SampleBigSet::KEY1 );
    $set2 = new SampleBigSet( SampleBigSet::KEY1 );
    $set3 = new SampleBigSet( SampleBigSet::KEY2 );
    
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
    
    $this->set->test0 = true;
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    $this->set->test0 = false;
    $this->assertFalse( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    $this->set->KEY1 = true;
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    $this->set->KEY1 = false;
    $this->assertFalse( $this->set->hasVal( SampleBigSet::KEY1 ));    
  }
  
  
  /**
   * Tests the __get magic method.
   * Supplied property name to set may be a constant from the class or the member name string.
   * Expected return value is the integer value of the member 
   */
  public function testMagicGet() : void
  {
    $this->assertGreaterThan( 0, $this->set->test0 );
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
    $this->set->add( SampleBigSet::KEY1 );
    
    $this->assertTrue( isset( $this->set->test0 ));
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
    $this->set->add( SampleBigSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    unset( $this->set->test0 );
    $this->assertFalse( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    $this->set->add( SampleBigSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    unset( $this->set->KEY1 );
    $this->assertFalse( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    //..No error expected 
    unset( $this->set->KEY1 );
    
    $this->expectException( InvalidArgumentException::class );
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
    $this->set->add( SampleBigSet::KEY1 );
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    
    $this->assertTrue( $this->set->KEY1());
    $this->assertTrue( $this->set->test0());
    $this->assertFalse( $this->set->KEY2());
    
    $this->expectException( InvalidArgumentException::class );
    $this->set->Invalid();
  }
  
  
  /**
   * Tests the __toString() magic method.
   * Expects a comma-delimited list of active members.
   * @return void
   */
  public function testMagicToString() : void
  {
    $this->set->clear();
    $this->set->add( SampleBigSet::KEY1, SampleBigSet::KEY2 );
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY1 ));
    $this->assertTrue( $this->set->hasVal( SampleBigSet::KEY2 ));
    
    $this->assertEquals( implode( ',', [SampleBigSet::KEY1, SampleBigSet::KEY2] ), (string)$this->set );
  }
  
  
  
  /**
   * Test that members can be added to an empty bigset
   * @return void
   */
  public function testCreateEmptyAddMember() : void
  {
    $s = new \buffalokiwi\buffalotools\types\BigSet();
    
    //..Internally, this caches the result, so calling this, then calling addMember 
    //  causes the next call to isMember to return the cached value of false
    $this->assertFalse( $s->isMember( 'test' ));
    
    $s->addMember( 'test' );
    
    $this->assertTrue( $s->isMember( 'test' ));
    $this->assertTrue( in_array( 'test', $s->getMembers()));    
  }
}
