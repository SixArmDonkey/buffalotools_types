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

use InvalidArgumentException;
use JsonSerializable;


/**
 * Defines an enum
 */
interface IEnum extends JsonSerializable
{ 
  /**
   * Sets the on change event.  This can attach multiple events.
   * @param \Closure $onChange on change f( IEnum $enum, string $oldValue, string $newValue ) : void
   * @return void
   */
  public function setOnChange( \Closure $onChange ) : void;
  
  
  /**
   * Sets the current enum value
   * @param string $p Enum value
   * @throws \InvalidArgumentException if $p is not a member of this enum 
   */
  public function setValue( string $p ) : void;

  
  /**
   * Retrieve the value of this enum
   * @return string value 
   */
  public function value() : string;

  
  /**
   * Retreieve the list of valid values
   * @return array string[] values 
   */
  public function getEnumValues() : array;
  
  
  /**
   * Retrieve a list of stored values 
   * @return type
   */
  public function getStoredValues();  

  
  /**
   * Detect if a value is a valid member of this enum.
   * @param string $v Possible enum value
   * @return bool is valid
   * @throws InvalidArgumentException if $v is empty
   */
  public function isValid( string $v ) : bool;
  
  
  /**
   * This will check to see if the current enum value is equal to one of the values in the list.
   * @param string $values One or more potential values (NOT the constant names that would be used for isset(), use the constant values or the constants themselves )
   * @return bool
   */
  public function is( string ...$values ) : bool;  
  
  
  /**
   * Retrieve the associated value for an enum constant
   * @return mixed value
   */
  public function getStoredValue();  
  

  /**
   * Tests that two enum objects have an equal type and value.
   * This uses a strict comparison on the values.
   * @param \buffalokiwi\buffalotools\types\IEnum $that Test against this enum.
   * @return bool
   */
  public function equals( IEnum $that ) : bool;
  
  
  /**
   * Comparison function.
   * 
   * Sorts by index value.
   * 
   * @param \buffalokiwi\buffalotools\types\IEnum $that Compare to that 
   * @return int -1,0,1
   * @throws InvalidArgumentException
   */
  public function compare( IEnum $that ) : int;
  
  
  /**
   * Compare the selected index value of two enums of the same type.
   * Test that the internal index is less than the supplied enum index.
   * 
   * ie: Enum {
   *   value1
   *   value2
   * }
   * 
   * e1 = value1
   * e2 = value2
   * 
   * e1->lessThan(e2) == true
   * e1->lessThan(e2) == false; they are equal.
   *
   * There needs to be another lessThan method that accepts a string.
   * 
   * @param \buffalokiwi\buffalotools\types\IEnum $that
   * @return bool
   */
  public function lessThan( IEnum $that ) : bool;
  
  
  /**
   * Compare the selected index value of 2 enums of the same type and test
   * that the current index is greater than the supplied enum index.
   * 
   * There needs to be another greaterThan method that accepts a string.
   * 
   * @param \buffalokiwi\buffalotools\types\IEnum $that
   * @return bool
   */
  public function greaterThan( IEnum $that ) : bool;
  
  
  /**
   * Retrieve the index value of the supplied enum value.
   * @param string $value Value or constant to test
   * @return int index or -1 
   */
  public function indexOf( string $p ) : int;  
  
  
  /**
   * Test if the enum value changed from one state to another during this 
   * runtime.  This only accepts enum string values, not the object constant names.
   * @param string $value Value this enum may have had.
   * @param string $from Change from value
   * @param string $to change to value 
   * @param bool $strict If enabled, this will check that $from changed directly to $to.  Otherwise, this will ensure 
   * that the enum at one point had a value of $from and that it had also been equal to $to at some point.
   * @return bool
   */
  public function changedFromTo( string $from, string $to, bool $strict = false ) : bool;
  
  
  /**
   * If the order status moved into this state during the objects lifetime.
   * @param string $to Moved to this 
   * @return bool Did it move?
   */
  public function changedTo( string $to ) : bool;  
  
  
  /**
   * Retrieve a chronological list of all changes to this enum.
   * @return array changes [from] => to
   */
  public function getChanges() : array;  
  
  
  /**
   * Retrieve the enum value by a stored value.
   * @param mixed $value value 
   * @return string enum value 
   */
  public function getByStoredValue( $value ) : string;  
  
  
  /**
   * Set the enum value to the next available state in the list.
   * If no additional values are available, this does nothing and returns
   * the current value.
   * @return string new value 
   */
  public function moveNext() : string;
  
  
  /**
   * Set the enum value to the previous state in the list.
   * If no additional values are available, this does nothing and returns
   * the first value in the enum list..
   * @return string new value 
   */
  public function movePrevious() : string;  
  
  
  /**
   * Compare function.
   * Compares enum values.
   * @param string $that compare to 
   * @return int -1,0,1
   */
  public function compareValues( string $that ) : int;
  
  
  public function lessThanValue( string $that ) : bool;
  
  
  public function greaterThanValue( string $that ) : bool;  
  
  
  /**
   * Set the enum as read only 
   * @return void
   */
  public function lock() : void;  
}
