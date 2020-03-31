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

use Exception;
use InvalidArgumentException;
use buffalokiwi\buffalotools\types\Set;


/**
 * This is a set that can store values with the bits that are set.
 * This class is not covered by tests, but should theoretically work.
 * 
 * This adds the get() method to ISet, and can be used to retrieve the value attached to a named bit.
 * 
 * Usage:
 * 
 * 
 * class MapSetImpl extends MapSet
 * {
 *   protected array $members = [
 *     'bit1' => 'value1',
 *     'bit2' => 'value2'
 *   ];
 * }
 * 
 * $set = new MapSetImpl();
 * $set->get( 'bit1' ); //..returns 'value1'
 * 
 */
class MapSet extends Set implements IMapSet
{
  /**
   * A map of values for constants in the set
   * @var array
   */
  protected array $map = [];


  /**
   * Constructor
   * @param array $map A key / value map where the keys are constants
   * (the constant value of the set member) in the set
   * and value are the values for each key that is enabled.
   * @throws InvalidArgumentException if any constants contain characters other
   * than [a-zA-Z0-9]
   */
  public function __construct( array $map = [] )
  {
    //..Initialize the set
    parent::__construct( array_keys( $map ) );

    //..Set the values for any members that were valid
    foreach ( $map as $k => $v )
    {
      //..Check the key
      if ( $this->isMember( $k ) )
      {
        //..It's valid, and enabled.  set the value
        $this->map[$k] = $v;
      }
    }
  }


  /**
   * Retrieve the internal map data
   * @return array map
   */
  public function map() : array
  {
    $out = [];
    foreach( $this->getActiveMembers() as $member )
    {
      if ( isset( $this->map[$member] ))
        $out[$member] = $this->map[$member];
    }
    return $out;
  }


  /**
   * Set value is changed to zero and map is cleared.
   */
  public function clear() : void
  {
    parent::clear();
    $this->map = array();
  }


  /**
   * Sets variable in the set to true and sets a value with it
   * @param string $const Bits to set.  This accepts a comma-delimited list of
   * string constants from members array. (as a string)
   * @param mixed $value the value
   * @throws InvalidArgumentException if a header is not a member of the class
   */
  public function addMap( string $const, $value ) : void 
  {
    parent::add( $const );
    $this->map[$this->getKeyFromArgument( $const )] = $value;
  }


  /**
   * Retrieve the value for the constant
   * @param string $const Set constant
   * @return mixed Map value or null if not set
   * @throws InvalidArgumentException if $const is not a set member
   */
  public function get( string $const )
  {
    if ( !$this->isMember( $const ) )
      throw new \InvalidArgumentException( $const . ' is not a member of this set' );

    $key = $this->getKeyFromArgument( $const );
    if ( isset( $this->map[$key] ) )
      return $this->map[$key];
    else
      return null;
  }
}
