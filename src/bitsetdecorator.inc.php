<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <john@retail-rack.com>
 * 
 * @author John Quinn
 */

declare( strict_types=1 );

namespace buffalokiwi\buffalotools\types;


class BitSetDecorator implements \buffalokiwi\buffalotools\types\IBitSet
{
  private IBitSet $set;
  
  public function __construct( IBitSet $set )
  {
    $this->set = $set;
  }  
  
  
  /**
   * Test if some bit is enabled by index position
   * @param int $position position
   * @return boolean is enabled 
   */
  public function isEnabledAt( int $position ) : bool
  {
    return $this->set->isEnabledAt( $position );
  }
  
   
  /**
   * Toggle some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function toggleAt( int $position ) : void
  {
    $this->set->toggleAt( $position );
  }
  
  
  /**
   * Enable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function enableAt( int $position ) : void
  {
    $this->set->enableAt( $position );
  }
  
  
  /**
   * Disable some bit by index position 
   * @param int $position position 
   * @return void
   */
  public function disableAt( int $position ) : void
  {
    $this->set->disableAt( $position );
  }
  
  
  /**
   * Set the internal value to a new value
   * @param int $value Value to set the mask to
   */
  public function setValue( int $value ) : void
  {
    $this->set->setValue( $value );
  }


  /**
   * Set the value of the bitset to the value of a different bitset.
   * @param IBitSet $that Other bitset
   */
  public function setValueOf( IBitSet $that ) : void
  {
    $this->set->setValueOf( $that );
  }


  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void
  {
    $this->set->clear();
  }
  

  /**
   * Detect if a flag is on or not
   * @param int $const Constant to check
   * @return boolean is set
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function isEnabled( int $const ) : bool
  {
    return $this->set->isEnabledAt( $const );
  }


  /**
   * Toggle a permission
   * @param int $const Permission to toggle
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function toggle( int $const ) : void
  {
    $this->set->toggle( $const );
  }


  /**
   * Enables a bit in the mask
   * @param int $const bit to enable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function enable( int $const ) : void
  {
    $this->set->enbable( $const );
  }


  /**
   * Disables a bit in the mask
   * @param int $const bit to disable
   * @throws \InvalidArgumentException if $const is not base2 
   */
  public function disable( int $const ) : void
  {
    $this->set->disable( $const );
  }
}
