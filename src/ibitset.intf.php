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

namespace buffalokiwi\buffalotools\types;


/**
 * A simple bit set.
 * Internally, this BitSet is a single integer, which can then be used as 32 or 64 individual boolean values.
 * The BitSet class is a wrapper for those operations.
 */
interface IBitSet
{
  /**
   * Test if some bit is enabled by index position
   * @param int $position position
   * @return boolean is enabled 
   */
  public function isEnabledAt( int $position ) : bool;
  
   
  /**
   * Toggle some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function toggleAt( int $position ) : void;
  
  
  /**
   * Enable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function enableAt( int $position ) : void;
  
  
  /**
   * Disable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function disableAt( int $position ) : void;
  
  /**
   * Set the internal value to a new value
   * @param int $value Value to set the mask to
   */
  public function setValue( int $value ) : void;


  /**
   * Set the value of the bitset to the value of a different bitset.
   * @param IBitSet $that Other bitset
   */
  public function setValueOf( IBitSet $that ) : void;


  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void;
  

  /**
   * Detect if a flag is on or not
   * @param int $const Constant to check
   * @return boolean is set
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function isEnabled( int $const );


  /**
   * Toggle a permission
   * @param int $const Permission to toggle
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function toggle( int $const );


  /**
   * Enables a bit in the mask
   * @param int $const bit to enable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function enable( int $const );


  /**
   * Disables a bit in the mask
   * @param int $const bit to disable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function disable( int $const );
}
