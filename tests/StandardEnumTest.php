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
use buffalokiwi\buffalotools\types\RuntimeEnum;

require_once( __DIR__ . '/AbstractEnumTest.php' );
require_once( __DIR__ . '/SampleEnum.php' );
require_once( __DIR__ . '/ValuedEnum.php' );


class StandardEnumTest extends AbstractEnumTest
{
  /**
   * Create an IEnum instance for testing
   * @return IEnum enum  
   */
  protected function createIEnumInstance() : IEnum
  {
    return new SampleEnum();
  }
  
  
  /**
   * Tests the enum constructor to ensure that it works.
   * Tests that supplied values are initialized in the enum.
   * Tests that the constructor can set the initial value
   * @return void
   */
  public function testEnumConstructor() : void
  {
    $enum = new RuntimeEnum( ['value1'] );
    $this->assertEquals( '', $enum->value());
    
    $enum = new RuntimeEnum( ['value1', 'value2'], 'value2' );
    $this->assertEquals( 'value2', $enum->value());
    
    $values = $enum->getEnumValues();
    $this->assertIsArray( $values );
    $this->assertEquals( 2, sizeof( $values ));
    $this->assertTrue( in_array( 'value1', $values ));
    $this->assertTrue( in_array( 'value2', $values ));    
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
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    
    $enum->KEY1;
    
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    
    $this->expectException( InvalidArgumentException::class );
    $enum->badValue;
  }
  
  
  /**
   * Tests that the enum value is returned when cast to a string
   * @return void
   */
  public function testMagicToString() : void
  {
    $this->instance->setValue( SampleEnum::KEY1 );
    $this->assertEquals( SampleEnum::KEY1, (string)$this->instance );
  }
  
  
  /**
   * Tests that some value equals the current enum 
   * @return void
   */
  public function testMagicCall() : void
  {
    $this->instance->setValue( SampleEnum::KEY1 );
    $this->assertTrue( $this->instance->KEY1());    
    $this->assertTrue( $this->instance->value1());
  }
  
  
  /**
   * Test creating enum instances via static method 
   * @return void
   */
  public function testMagicCallStatic() : void
  {
    $enum = SampleEnum::KEY1();
    $this->assertInstanceOf( SampleEnum::class, $enum );
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    
    $this->expectException( InvalidArgumentException::class );
    SampleEnum::INVALID_KEY();
  }
  
  
  /**
   * Enums can use isset to test the value 
   * @return void
   */
  public function testMagicIsset() : void
  {
    $this->instance->setValue( SampleEnum::KEY1 );
    $this->assertEquals( SampleEnum::KEY1, $this->instance->value());
    
    $this->assertTrue( isset( $this->instance->KEY1 ));
    $this->assertTrue( isset( $this->instance->value1 ));
    $this->assertFalse( isset( $this->instance->KEY2 ));
    
    //..Expects a E_USER_WARNING 
    $this->expectWarning();
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
  
  
  
  public function testOnChange() : void
  {
    $enum = new SampleEnum( SampleEnum::KEY1 );
    
    $i = 0;    
    $enum->setOnChange( function( SampleEnum $e, string $oldValue, string $newValue ) use (&$i) : void {
      $this->assertEquals( SampleEnum::KEY1, $oldValue );
      $this->assertEquals( SampleEnum::KEY2, $newValue );
      $i++;
    });
    
    $enum->setValue( SampleEnum::KEY2 );
    $this->assertEquals( 1, $i );
    
  }
  
  
  /**
   * Tests that the enum will track changes
   * @return void
   */
  public function testChangeTo() : void
  {
    $enum = new SampleEnum( SampleEnum::KEY1 );
    $this->assertFalse( $enum->changedTo( SampleEnum::KEY1 ));
    $this->assertTrue( empty( $enum->getChanges()));
    
    $enum->setValue( SampleEnum::KEY2 );
    $this->assertTrue( $enum->changedTo( SampleEnum::KEY2 ));
    $this->assertFalse( $enum->changedTo( SampleEnum::KEY1 ));    
    
    
    
    $enum = new SampleEnum( SampleEnum::KEY1 );
    $enum->setOnChange( function( SampleEnum $e, string $oldValue, string $newValue ) : void {
      throw new \Exception();
    });
        
    $this->expectException( \Exception::class );
    $enum->setValue( 'dummy' );
    
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    $this->assertFalse( in_array( 'dummy', $enum->getChanges()));
  }
  
  
  /**
   * Test that the enum tracks changes when a state changes from something to something else.
   */
  public function testChangeFromTo() : void
  {
    $enum = new SampleEnum( SampleEnum::KEY1 );
    $this->assertTrue( empty( $enum->getChanges()));
    
    $enum->setValue( SampleEnum::KEY2 );
    $this->assertTrue( $enum->changedFromTo( SampleEnum::KEY1, SampleEnum::KEY2 ));

    $enum->setValue( SampleEnum::KEY3 );
    $this->assertTrue( $enum->changedFromTo( SampleEnum::KEY1, SampleEnum::KEY2 ));
    $this->assertTrue( $enum->changedFromTo( SampleEnum::KEY2, SampleEnum::KEY3 ));
    $this->assertTrue( $enum->changedFromTo( SampleEnum::KEY1, SampleEnum::KEY3 ));        
    
    
    $enum = new SampleEnum( SampleEnum::KEY1 );
    $enum->setValue( SampleEnum::KEY2 );
    $enum->setValue( SampleEnum::KEY1 );
    $enum->setValue( SampleEnum::KEY3 );
    
    $this->assertTrue( $enum->changedFromTo( SampleEnum::KEY1, SampleEnum::KEY2 ));
    
  }
  

  public function testIndexOf() : void
  {
    $enum1 = new SampleEnum();    
    
    $this->assertEquals( 0, $enum1->indexOf( SampleEnum::KEY1 ));
    $this->assertEquals( 1, $enum1->indexOf( SampleEnum::KEY2 ));
    $this->assertEquals( 2, $enum1->indexOf( SampleEnum::KEY3 ));
  }

  
  /**
   * Tests that movenext causes the enum to move to the next available status 
   * @return void
   */
  public function testMoveNext() : void
  {
    $enum = new SampleEnum( SampleEnum::KEY1 );
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    
    $enum->moveNext();
    $this->assertEquals( SampleEnum::KEY2, $enum->value());
    
    $enum->moveNext();
    $this->assertEquals( SampleEnum::KEY3, $enum->value());    
    
    $enum->moveNext();
    $this->assertEquals( SampleEnum::KEY3, $enum->value());        
  }
  
  
  /**
   * Tests the movePrevious() method.
   * This should move to the previous value by index.
   * @return void
   */
  public function testMovePrevious() : void
  {
    $enum = new SampleEnum( SampleEnum::KEY3 );
    $this->assertEquals( SampleEnum::KEY3, $enum->value());
    
    $enum->movePrevious();
    $this->assertEquals( SampleEnum::KEY2, $enum->value());
    
    $enum->movePrevious();
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
    
    $enum->movePrevious();
    $this->assertEquals( SampleEnum::KEY1, $enum->value());
  }
  
  
  public function testCompare() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum1a = new SampleEnum( SampleEnum::KEY1 );
    
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
    $enum3 = new SampleEnum( SampleEnum::KEY3 );
    
    $this->assertEquals( -1, $enum1->compare( $enum2 ));
    $this->assertEquals( -1, $enum1->compare( $enum3 ));
    $this->assertEquals( 0, $enum1->compare( $enum1a ));
    $this->assertEquals( 1, $enum2->compare( $enum1 ));
  }
  
  
  public function testCompareValues() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
    
    $this->assertEquals( -1, $enum1->compareValues( SampleEnum::KEY2 ));
    $this->assertEquals( -1, $enum1->compareValues( SampleEnum::KEY3 ));
    $this->assertEquals( 0, $enum1->compareValues( SampleEnum::KEY1 ));
    $this->assertEquals( 1, $enum2->compareValues( SampleEnum::KEY1 ));
  }
  
  
  public function testLessThan() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
  
    $this->assertTrue( $enum1->lessThan( $enum2 ));
    $this->assertFalse( $enum2->lessThan( $enum1 ));
  }
  
  
  public function testLessThanValue() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
  
    $this->assertTrue( $enum1->lessThanValue( SampleEnum::KEY2 ));
    $this->assertFalse( $enum2->lessThanValue( SampleEnum::KEY1 ));    
  }
  
  
  public function testGreaterThan() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
  
    $this->assertFalse( $enum1->greaterThan( $enum2 ));
    $this->assertTrue( $enum2->greaterThan( $enum1 ));    
  }
  
  
  public function testGreaterThanValue() : void
  {
    $enum1 = new SampleEnum( SampleEnum::KEY1 );
    $enum2 = new SampleEnum( SampleEnum::KEY2 );
  
    $this->assertFalse( $enum1->greaterThanValue( SampleEnum::KEY2 ));
    $this->assertTrue( $enum2->greaterThanValue( SampleEnum::KEY1 ));        
  }
  
  
}
