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
use buffalokiwi\buffalotools\types\MultiValueEnum;
use buffalokiwi\buffalotools\types\RuntimeEnum;



class MultiEnum extends MultiValueEnum 
{
  const KEY1 = ['value1','k1'];
  const KEY2 = ['value2','k2'];
  
  protected array $enum = [
    self::KEY1,
    self::KEY2
  ];
}


class MultiValueEnumTest extends AbstractEnumTest
{
  /**
   * Create an IEnum instance for testing
   * @return IEnum enum  
   */
  protected function createIEnumInstance() : IEnum
  {
    return new MultiEnum();
  }
  
  
  /**
   * Test the getStoredValue() method 
   */
  public function testGetStoredValue() : void
  {
    $this->assertEquals( null, $this->instance->getStoredValue());
  }
  
  
  /**
   * Tests the __get magic method.
   * This MUST set the value of the enum 
   * @return void
   */
  public function testMagicGet() : void
  {
    $enum = $this->createIEnumInstance();
    $enum->value1;
    $this->assertEquals( MultiEnum::KEY1[0], $enum->value());
    
    $enum->KEY1;
    
    $this->assertEquals( MultiEnum::KEY1[0], $enum->value());
    
    $this->expectException( InvalidArgumentException::class );
    $enum->badValue;
  }
  
  
  /**
   * Tests that the enum value is returned when cast to a string
   * @return void
   */
  public function testMagicToString() : void
  {
    $this->instance->setValue( MultiEnum::KEY1[0] );
    $this->assertEquals( MultiEnum::KEY1[0], (string)$this->instance );
  }
  
  
  /**
   * Tests that some value equals the current enum 
   * @return void
   */
  public function testMagicCall() : void
  {
    $this->instance->setValue( MultiEnum::KEY1[0] );
    $this->assertTrue( $this->instance->KEY1());    
    $this->assertTrue( $this->instance->value1());
  }
  
  
  /**
   * Test creating enum instances via static method 
   * @return void
   */
  public function testMagicCallStatic() : void
  {
    $enum = MultiEnum::KEY1();
    $this->assertInstanceOf( MultiEnum::class, $enum );
    $this->assertEquals( MultiEnum::KEY1[0], $enum->value());
    
    $this->expectException( InvalidArgumentException::class );
    MultiEnum::INVALID_KEY();
  }
  
  
  /**
   * Enums can use isset to test the value 
   * @return void
   */
  public function testMagicIsset() : void
  {
    $this->instance->setValue( MultiEnum::KEY1[0] );
    $this->assertEquals( MultiEnum::KEY1[0], $this->instance->value());
    
    $this->assertTrue( isset( $this->instance->KEY1 ));
    $this->assertTrue( isset( $this->instance->value1 ));
    $this->assertFalse( isset( $this->instance->KEY2[1] ));
    
    $this->expectException( InvalidArgumentException::class );
    isset( $this->instance->invalidValue );    
  }
  
  
  /**
   * Tests the enum constructor to ensure that it works.
   * Tests that supplied values are initialized in the enum.
   * Tests that the constructor can set the initial value
   * @return void
   */
  public function testValuedEnumConstructor() : void
  {
    $enum = new ValuedEnum();
    $this->assertEquals( '', $enum->value());
    
    $enum = new ValuedEnum( ValuedEnum::KEY2 );
    $this->assertEquals( ValuedEnum::KEY2, $enum->value());
    
    $values = $enum->getEnumValues();
    $this->assertIsArray( $values );
    $this->assertEquals( 2, sizeof( $values ));
    $this->assertTrue( in_array( ValuedEnum::KEY, $values ));
    $this->assertTrue( in_array( ValuedEnum::KEY2, $values ));    
  }
  
  
  /**
   * Test the getStoredValue() method 
   */
  public function testValuedEnumGetStoredValue() : void
  {
    $enum = new ValuedEnum();
    $enum->setValue( ValuedEnum::KEY );
    $this->assertEquals( ValuedEnum::VALUE, $enum->getStoredValue());
    
    $enum->setValue( ValuedEnum::KEY2 );
    $this->assertEquals( ValuedEnum::VALUE2, $enum->getStoredValue());        
  }  
}
