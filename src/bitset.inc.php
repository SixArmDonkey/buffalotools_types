<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <johnquinn3@gmail.com>
 *  Launcher
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\buffalotools\types;

use InvalidArgumentException;


/**
 * A simple bit set.
 * 
 * Internally, this BitSet is a single integer, which can then be used as 32 or 64 individual boolean values.
 * The BitSet class is a wrapper for those operations.
 * 
 * For example:
 * 
 * Create a new and empty BitSet.
 * This will have either 32 or 64 possible values based on which architecture the installed version of PHP is using.
 * 
 * $b = new BitSet( 0 );  
 * 
 * //..Enable bits:
 * $b->enable( 0x2 ); //..Bit 2 is now enabled 
 * $b->enableAt( 2 ); //..Bit 2 is enabled 
 * $b->setValue( 2 ); //..Bit 2 is enabled (This is the sum of all enabled bits)
 * 
 * //..Disable bits:
 * $b->disable( 0x2 ); //..Bit 2 is now disabled 
 * $b->disableAt( 2 ); //..Bit 2 disabled
 * $b->setValue( 0 ); //..All bits are disabled 
 * 
 * //..Toggle bits:
 * $b->enable( 0x2 ); //..Enable bit 2
 * $b->toggle( 0x2 ); //..Bit 2 is now disabled 
 * 
 * //..Testing if bits are enabled:
 * $b->enable( 0x2 );
 * $b->isEnabled( 0x2 ); //..Returns true
 * $b->isEnabledAt( 2 ); //..Returns true 
 * 
 * //..Retrieving the internal value:
 * //..The internal value is the sum of all enabled bits 
 * 
 * $b->clear(); //..Disables all bits 
 * $b->getValue(); //.Returns zero
 * $b->enable( 0x1 ); //..Enable bit 1
 * $b->getValue(); //..Returns one
 * $b->enable( 0x2 ); //..Enable bit 2
 * $b->getValue(); //..Returns three
 * $b->disable( 0x1 ); //..Disable bit 1
 * $b->getValue(); //..Returns two 
 * 
 * 
 * 
 * 
 * 
 */
class BitSet implements IBitSet
{
  //..Listing the first 31 values as sane defaults.
  private static array $RUNTIME = [
    0x1 => true,
    0x2 => true,
    0x4 => true,
    0x8 => true,
    0x10 => true,
    0x20 => true,
    0x40 => true,
    0x80 => true,
    0x100 => true,
    0x200 => true,
    0x400 => true,
    0x800 => true,    
    0x1000 => true,
    0x2000 => true,
    0x4000 => true,
    0x8000 => true,
    0x10000 => true,
    0x20000 => true,
    0x40000 => true,
    0x80000 => true,
    0x100000 => true,
    0x200000 => true,
    0x400000 => true,
    0x800000 => true,
    0x1000000 => true,
    0x2000000 => true,
    0x4000000 => true,
    0x8000000 => true,   
    0x10000000 => true,
    0x20000000 => true,
    0x40000000 => true
   ];

  
  /**
   * The total value
   * 
   * @var int
   */
  protected int $value = 0;


  /**
   * Initialize the set to a value.
   * @param int $value value
   * @throws \InvalidArgumentException if value is less than zero 
   */
  public function __construct( int $value )
  {
    if ( $value < 0 )
      throw new InvalidArgumentException( 'value must be greater than zero' );
    
    $this->value = $value;
  }


  /**
   * Set the internal value to a new value
   * @param int $value Value to set the mask to
   */
  public function setValue( int $value ) : void
  {
    $this->value = $value;
  }
  
  
  /**
   * Retrieve the bitmask value of the set
   * @return int Mask Value
   * @final 
   */
  public final function getValue()
  {
    return $this->value;
  }

  
  /**
   * Set the value of the bitset to the value of a different bitset.
   * @param IBitSet $that Other bitset
   */
  public function setValueOf( IBitSet $that ) : void
  {
    $this->setValue( $that->getValue());
  }


  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void
  {
    $this->value = 0;
  }
  

  /**
   * Detect if a flag is on or not
   * @param int $const Constant to check
   * @return boolean is set
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function isEnabled( int $const )
  {
    return (( $this->value & $const ) == $const );
  }

  
  /**
   * Test if some bit is enabled by index position
   * @param int $position position
   * @return boolean is enabled 
   */
  public function isEnabledAt( int $position ) : bool
  {
    return ( $this->value & ( 1 << $position )) != 0;
  }
  
   
  /**
   * Toggle some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function toggleAt( int $position ) : void
  {
    $this->value ^= ( 1 << $position );
  }
  
  
  /**
   * Enable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function enableAt( int $position ) : void
  {
    $this->value |= ( 1 << $position );
  }
  
  
  /**
   * Disable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function disableAt( int $position ) : void
  {
    $this->value &= ~( 1 << $position );
  }

  /**
   * Toggle a permission
   * @param int $const Permission to toggle
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function toggle( int $const )
  {
    if ( $this->isEnabled( $const ) )
      $this->value -= $const;
    else
      $this->value += $const;
  }


  /**
   * Enables a bit in the mask
   * @param int $const bit to enable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function enable( int $const )
  {
    if ( !$this->isEnabled( $const ) )
      $this->value += $const;
  }


  /**
   * Disables a bit in the mask
   * @param int $const bit to disable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function disable( int $const )
  {
    if ( $this->isEnabled( $const ) )
      $this->value -= $const;
  }
  
  
  /**
   * Detect if an integer is binary or not
   * Uses some nifty bit-hackery.
   * If i is even then i & ( i - 1 ) == 0
   * Look at that in base 2, it's neat to see it in action :)
   * @param int $i Integer to test
   * @return boolean is base 2
   * @final
   */
  protected final function isBase2( $i ) : bool
  {
    if ( isset( self::$RUNTIME[$i] ))
      return self::$RUNTIME[$i];
    else if ( !ctype_digit((string)$i ))
    {
      self::$RUNTIME[$i] = false;
      return false;
    }
    
    $i = (int)$i;
    $res = ( $i != 0 ) && (( $i & ( $i - 1 )) == 0 );
    
    self::$RUNTIME[$i] = $res;
    
    return $res;
  }
}
