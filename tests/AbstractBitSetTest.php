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
use PHPUnit\Framework\TestCase;


/**
 * Tests the IBitSet interface 
 */
abstract class AbstractBitSetTest extends TestCase
{
  /**
   * IBitSet instance being tested 
   * @var IBitSet
   */
  protected $instance;
  
  
  /**
   * Create the IBitSet instance
   * @return IBitSet instance to test 
   */
  protected abstract function createIBitSetInstance() : IBitSet;
  
  
  /**
   * Create the bit set instance to test 
   * @return void
   */
  public function setUp() : void
  {
    $this->instance = $this->createIBitSetInstance();
  }
  
  
  /**
   * Tests the set value method and the getValue method 
   * This should be able to set any arbitrary integer as the value of the bit set 
   */
  public function testSetValue() : void
  {
    $this->instance->setValue( 10 );
    $this->assertEquals( 10, $this->instance->getValue());
    
    $this->instance->setValue( 20 );
    $this->assertEquals( 20, $this->instance->getValue());
    
    $this->expectException( TypeError::class );
    $this->instance->setValue( null );
  }
    
  
  /**
   * Tests the setValueOf method.
   * This should take any arbitrary integer value from any IBitSet instance and 
   * set it as the value of the bit set being tested.
   */
  public function testValueOf() : void
  {
    $i2 = $this->createIBitSetInstance();
    $i2->setValue( 10 );
    
    $this->instance->setValueOf( $i2 );
    $this->assertEquals( 10, $this->instance->getValue());
  }
  
  
  /**
   * Tests the clear method.
   * Expects value to be set to zero.
   * @return void
   */  
  public function testClear() : void
  {
    $this->instance->setValue( 10 );
    $this->assertEquals( 10, $this->instance->getValue());
    
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
  }
  
  
  
  /**
   * Tests the is enabled method.
   * Setting the value to some sum of base 2 integers must cause isEnabled 
   * to return true when any of the supplied base 2 integers are supplied as an 
   * argument.
   * @return void
   */
  public function testIsEnabled() : void
  {
    $this->instance->setValue( 2 );
    $this->assertEquals( true, $this->instance->isEnabled( 0x2 ));
  }
  
  
  /**
   * Tests the toggle method.
   * Supplying any base 2 integer must increase and decrease the total bit set value
   * by the supplied integer.
   * @return void
   */
  public function testToggle() : void
  {
    $this->instance->setValue( 0 );
    $this->instance->toggle( 0x2 );
    $this->assertEquals( 2, $this->instance->getValue());
    
    $this->instance->toggle( 0x2 );
    $this->assertEquals( 0, $this->instance->getValue());
  }
  
  
  /**
   * Tests the enable method.
   * Supplying any base 2 integer as an argument must increase the total bit set
   * value by that amount.
   * @return void
   */
  public function testEnable() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    $this->instance->enable( 0x2 );
    $this->assertEquals( 2, $this->instance->getValue());
    
    $this->instance->enable( 0x4 );
    $this->assertEquals( 6, $this->instance->getValue());
  }
  
  
  /**
   * Tests the disable method.
   * Supplying any base 2 integer as an argument must decrease the total bit set
   * value by that amount.
   * @return void
   */
  public function testDisable() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    $this->instance->enable( 0x2 );
    $this->assertEquals( 2, $this->instance->getValue());
    
    $this->instance->enable( 0x4 );
    $this->assertEquals( 6, $this->instance->getValue());    
    
    $this->instance->disable( 0x8 );
    $this->assertEquals( 6, $this->instance->getValue());    
    
    $this->instance->disable( 0x4 );
    $this->assertEquals( 2, $this->instance->getValue()); 
  }
  
  
  public function testEnabledAt() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    
    $this->instance->enable( 0x2 );
    $this->assertTrue( $this->instance->isEnabledAt( 1 ));
  }
  
  
  public function testToggleAt() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    
    $this->instance->toggleAt( 1 );
    $this->assertTrue( $this->instance->isEnabledAt( 1 ));    
   
    $this->assertFalse( $this->instance->isEnabledAt( 10 ));
    
    $this->instance->toggleAt( 1 );
    $this->assertFalse( $this->instance->isEnabledAt( 1 ));    
  }
  
  
  public function testEnableAt() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    
    $this->instance->enableAt( 1 );
    $this->assertFalse( $this->instance->isEnabledAt( 10 ));
    $this->assertTrue( $this->instance->isEnabledAt( 1 ));        
  }
  

  public function testDisableAt() : void
  {
    $this->instance->clear();
    $this->assertEquals( 0, $this->instance->getValue());
    
    $this->instance->enableAt( 1 );
    $this->instance->enableAt( 10 );
    $this->assertTrue( $this->instance->isEnabledAt( 1 ));        

    $this->instance->disableAt( 1 );
    $this->assertTrue( $this->instance->isEnabledAt( 10 ));
    $this->assertFalse( $this->instance->isEnabledAt( 1 ));        
    
  }
}
