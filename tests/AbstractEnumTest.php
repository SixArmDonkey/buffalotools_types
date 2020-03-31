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

use buffalokiwi\buffalotools\types\IEnum;
use PHPUnit\Framework\TestCase;


abstract class AbstractEnumTest extends TestCase
{
  /**
   * Enum instance 
   * @var IEnum
   */
  protected $instance;
  
  /**
   * A valid element for the enum being tested
   * @var string
   */
  protected $element;
    
  /**
   * Create an IEnum instance for testing
   * @return IEnum enum  
   */
  protected abstract function createIEnumInstance() : IEnum;
  
  /**
   * Test the getStoredValue() method 
   */
  protected abstract function testGetStoredValue() : void;
  
  
  /**
   * Create an instance for testing.
   * This tests the getEnumValues() method 
   * @return void
   */
  public function setUp() : void
  {
    $this->instance = $this->createIEnumInstance();
    
    //..Retrieve all possible enum values and assign the first value to $element
    $values = $this->instance->getEnumValues();
    $this->assertIsArray( $values );
    $this->assertGreaterThan( 0, sizeof( $values ));
    $element = reset( $values );
    $this->assertIsString( $element );
    
    $this->element = $element;
  }
  
  
  /**
   * Tests that setting an invalid value throws an InvalidArgumentException
   * @return void
   */
  public function testSetInvalidValueFails() : void
  {
    $this->expectException( \InvalidArgumentException::class );
    $this->instance->setValue( 'not_a_real_value' );
  }    
  
  /**
   * Tests the value getter and setter methods.
   * @return void
   */
  public function testValue() : void
  {
    
    //..Set the enum value
    $this->instance->setValue( $this->element );
    
    //..Get the enum value 
    $this->assertEquals( $this->element, $this->instance->value());
    
    $this->expectException( TypeError::class );
    $this->instance->setValue( null );
  }
  
  
  /**
   * Tests the isValid method.
   * 
   * @return void
   */
  public function testIsValid() : void
  {
    $this->assertEquals( true, $this->instance->isValid( $this->element ));
    $this->assertEquals( false, $this->instance->isValid( 'not_a_real_element' ));
    
    $this->expectException( TypeError::class );
    $this->instance->isValid( null );
  }
  
  
  /**
   * Tests the is method 
   * @return void
   */
  public function testIs() : void
  {
    $this->instance->setValue( $this->element );
    $this->assertEquals( $this->element, $this->instance->value());
    
    $this->assertEquals( true, $this->instance->is( 'not_real_1', $this->element, 'not_real_2' ));
    $this->assertEquals( false, $this->instance->is( 'not_real_1', 'not_real_2' ));
    
    $this->expectException( TypeError::class );
    $this->instance->is( null );    
  }
}
