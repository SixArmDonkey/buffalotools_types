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
 * Defines a Set that can store values for enabled bits
 */
interface IMapSet extends ISet
{
  /**
   * Retrieve the value for the constant
   * @param string $const Set constant
   * @return mixed Map value or null if not set
   * @throws \InvalidArgumentException if $const is not a set member
   */
  public function get( string $const );


  /**
   * Sets variables in the set to true.
   * This accepts a varaible number of arguments (see $const param)
   * @param string $const Bits to set.  This accepts a comma-delimited list of
   * string constants from members array.
   * @param mixed $value the value
   * @throws \InvalidArgumentException if the member is not found in the set 
   */
  public function addMap( string $const, $value ) : void;

  
  /**
   * Retrieve the internal map data
   * @return array map
   */
  public function map() : array;
}