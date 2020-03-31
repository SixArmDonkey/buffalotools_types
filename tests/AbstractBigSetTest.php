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

use PHPUnit\Framework\TestCase;
use buffalokiwi\buffalotools\types\IBigSet;

/**
 * Tests some IBigSet instance.
 * The supplied IBigSet instance for testing MUST contain at least 2 members and 
 * those member names MUST be returned by IBigSet::getMembers().
 */
abstract class AbstractBigSetTest extends TestCase
{
  /**
   * Test set instance 
   * @var IBigSet
   */
  protected $set;
  
  /**
   * Member name 1
   * @var string
   */
  protected $member1;
  
  /**
   * Member name 2
   * @var string
   */
  protected $member2;
  
  /**
   * IBitSet instance being tested 
   * @var IBitSet
   */
  protected $instance;
  
  
  /**
   * Create the IBitSet instance
   * @return IBigSet instance to test 
   */
  protected abstract function createIBigSetInstance() : IBigSet;
  
  
  
  /**
   * Create the bit set instance to test 
   * @return void
   */
  public function setUp() : void
  {
    $this->instance = $this->createIBigSetInstance();
    $this->assertInstanceof( \buffalokiwi\buffalotools\types\IBigSet::class, $this->instance );
    $this->set = $this->instance;
    
    $members = $this->set->getMembers();
    $this->assertIsArray( $members );
    $this->assertGreaterThanOrEqual( 2, sizeof( $members ));
    $this->member1 = reset( $members );
    $this->member2 = end( $members );
    
    $this->assertIsString( $this->member1 );
    $this->assertIsString( $this->member2 );
    $this->assertNotEmpty( $this->member1 );
    $this->assertNotEmpty( $this->member2 );
  }  
  
  
  /**
   * Tests the isMember method.
   * Supplied argument may be a constant from the class or the member name string.
   */
  public function testIsMember() : void
  {
    $this->assertTrue( $this->set->isMember( $this->member1 ));
    $this->assertTrue( $this->set->isMember( $this->member2 ));    
    $this->assertTrue( $this->set->isMember( $this->member1, $this->member2 ));
    $this->assertFalse( $this->set->isMember( 'Invalid' ));
    $this->assertFalse( $this->set->isMember( $this->member1, $this->member2, 'Invalid' ));
    $this->expectException( TypeError::class );
    $this->set->isMember( null );
  }
  
  
  public function testAddMember() : void
  {
    $this->set->addMember( 'newmember' );
    $this->set->add( 'newmember' );
    $this->assertTrue( $this->set->hasVal( 'newmember' ));
  }

  
  /**
   * Tests that setting an invalid value throws an InvalidArgumentException
   * @return void
   */
  public function testSetInvalidValueFails() : void
  {
    $this->expectException( InvalidArgumentException::class );
    $this->set->add( 'invalid' );    
  }
  
  
  /**
   * Tests the add() method
   * 
   * Test adding one or more members to the set 
   * Accepts:
   * 1) multiple string arguments 
   * 
   * Supplied values may be a constant from the class or the member name string.
   * 
   * @return void
   */
  public function testAdd() : void
  {
    $this->set->clear();
    $this->assertEmpty( $this->set->getActiveMembers());
    
    $this->set->add( $this->member1 );
    $this->assertTrue( $this->set->hasVal( $this->member1 ));
    $this->assertFalse( $this->set->hasVal( $this->member2 ));
    
    $this->expectException( TypeError::class );
    $this->set->add( null );
  }
  
  
  
  public function testRemoveInvalidValueFails() : void
  {
    $this->expectException( InvalidArgumentException::class );
    $this->set->remove( 'invalid' );
  }    
  
  
  /**
   * Tests the remove() method 
   * 
   * Test removing one or more members to the set 
   * Accepts:
   * 1) multiple string arguments 
   * 
   * Supplied values may be a constant from the class or the member name string.
   * 
   * @return void
   */
  public function testRemove() : void
  {
    $this->set->clear();
    $this->set->add( $this->member1 );
    $this->assertTrue( $this->set->hasVal( $this->member1 ));
    $this->set->remove( $this->member1, $this->member2 );
    $this->assertEmpty( $this->set->getActiveMembers());
    
    $this->expectException( TypeError::class );
    $this->set->remove( null );
  }
  
  
  
  public function testHasValWithInvalidValueFails() : void
  {
    $this->expectException( InvalidArgumentException::class );
    $this->set->hasVal( 'invalid' );    
  }
  
  
  /**
   * Tests the hasVal() method
   * 
   * Test hasVal correctly identifies enabled members within the set.
   * 
   * Accepts:
   * 1) multiple string arguments 
   * 
   * Supplied values may be a constant from the class or the member name string.
   * 
   * @return void
   */
  public function testHasVal() : void
  {
    $this->set->clear();
    $this->assertEmpty( $this->set->getActiveMembers());
    $this->set->add( $this->member1 );
    
    $this->assertTrue( $this->set->hasVal( $this->member1 ));
    
    $this->assertTrue( $this->set->hasVal( $this->member1 ));
    $this->assertFalse( $this->set->hasVal( $this->member2 ));
    
    $this->expectException( TypeError::class );
    $this->set->hasVal( null );
  }
  
  
  /**
   * Tests the setAll method which sets all of the bits in teh set.
   * Calling this method must cause getValue() to equal getTotal().
   * 
   * @return void
   */
  public function testSetAll() : void
  {
    $this->set->setAll();  
    $this->assertEmpty( array_diff( $this->set->getActiveMembers(), $this->set->getMembers()));
  }
    
  
  
  /**
   * Tests the getActiveMembers() method.
   * Expects a list of strings containing enabled member names.
   * 
   * @return void
   */
  public function testGetActiveMembers() : void
  {
    $this->set->clear();
    $this->assertEmpty( $this->set->getActiveMembers());
    $this->set->add( $this->member1 );
    $this->set->add( $this->member2 );
    
    $res = $this->set->getActiveMembers();
    $this->assertIsArray( $res );
    $this->assertEquals( 2, sizeof( $res ));
    
    $this->assertTrue( in_array( $this->member1, $res ));
    $this->assertTrue( in_array( $this->member2, $res ));
  }
  
  
  /**
   * Tests the getMembers() method.
   * Expects a list of strings containing every member for the set.
   */
  public function testGetMembers() : void
  {
    $res = $this->set->getMembers();
    $this->assertIsArray( $res );
    
    $this->assertTrue( in_array( $this->member1, $res ));
    $this->assertTrue( in_array( $this->member2, $res ));
  }
  
  
  /**
   * Tests the isEmpty() method.
   * Expects the set to have zero active members and a bit set value of zero
   * when empty.
   */
  public function testIsEmpty() : void
  {
    $this->set->clear();
    $this->assertTrue( $this->set->isEmpty());
    $this->assertEmpty( $this->set->getActiveMembers());
  }
}
