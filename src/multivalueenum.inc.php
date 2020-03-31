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


/**
 * An Enum that can contain many values.
 * For example, say you wanted to list all of the states in the United States, but wanted 
 * both the abbreviation and full names.
 * 
 * 
 * class EState extends MultiValueEnum implements IState
 * {
 *   const ALABAMA = ['AL','Alabama'];
 *   //..Other states
 *   const TEXAS = ['TX', 'Texas'];
 * 
 *   protected $enum = [self::ALABAMA];
 * 
 *   protected $value = self::ALABAMA;
 * 
 *   public function getFullName() : string {
 *     return $this->getStoredValue()[1];
 *   }
 * }
 * 
 * $state = new EState();
 * $state->value(); //..Returns 'AL'
 * $state->getFullName(); //..Returns 'Alabama';
 * $state->getStoredValue(); //..Returns ['AL','Alabama'];
 * 
 * WARNING:
 * 
 * This is still a prototype, and is based on Enum, so there are limitations.  See MultiValueEnumTest.
 * 
 * Whatever value is in position zero is the enum key.  The stored value is the entire array.
 *  
 */
class MultiValueEnum extends Enum implements IEnum
{
  private static array $RUNTIME = [];
  
  
  /**
   * A list of values for the enum
   * @var array
   */
  private array $values = [];

  private array $constants = [];
  
  /**
   * Constructor
   * @param string $init default value
   */
  public function __construct( string $init = '' )
  {
    $cls= static::class;
    if ( isset( self::$RUNTIME[$cls] ))
    {
      $this->enum   = self::$RUNTIME[$cls][0];
      $this->values = self::$RUNTIME[$cls][1];
      $this->value  = self::$RUNTIME[$cls][2];
      $this->constants = self::$RUNTIME[$cls][3];
    }
    else
    {
      //..DO NOT CALL THE PARENT CONSTRUCTOR 
      $this->init( $init );
      self::$RUNTIME[$cls][0] = $this->enum;
      self::$RUNTIME[$cls][1] = $this->values;
      self::$RUNTIME[$cls][2] = $this->value;
      self::$RUNTIME[$cls][3] = $this->constants;
    }    
  }


  /**
   * Sets the current enum value.
   * This accepts a constant name or a constant value
   * @param string $p Enum value
   * @return IEnum The current instance
   * @final
   */
  public final function __get( string $p ) : IEnum
  {
    if ( isset( $this->constants[$p] ))
    {
      $val = $this->constants[$p];
      if ( !is_array( $val ) || empty( $val ))
        throw new InvalidArgumentException( $p . ' must be a non-empty array' );
      
      $this->setValue( $val[0] );
    }
    else
    {
      $this->setValue( $p );
    }

    return $this;
  }



  /**
   * Create a new Enum instance by constant.
   * ie Enum::CONSTANT() will return a valid Enum instance with the value of
   * the constant.
   * @param string $name Enum constant
   * @param mixed $arguments unused
   * @return \IEnum instance
   * @throws InvalidArgumentException
   * @static
   */
  public static function __callStatic( string $name, array $arguments ) : IEnum 
  {
    if ( !defined( 'static::' . $name ))
      throw new InvalidArgumentException( __CLASS__ . '::' . $name . ' is not a valid member of this enum' );

    $val = constant( 'static::' . $name );
    if ( !is_array( $val ) || empty( $val ))
      throw new InvalidArgumentException( $name . ' must be a non-empty array' );
    
    $obj = new static();
    $obj->setValue( $val[0] );
    return $obj;
  }


  /**
   * A way to determine if a enum is equal to a specific value.
   * ie:
   * if ( isset( $enum->TYPE ))
   *   echo 'is set';
   *
   * This will also test by value
   *
   * @param string $name Constant name
   * @return bool is set
   * @throws InvalidArgumentException if name is not a member of this enum 
   */
  public function __isset( string $name ) : bool
  {
    if ( defined( 'static::' . $name ))
    {
      $val = constant( 'static::' . $name );
      if ( !is_array( $val ) || empty( $val ))
        throw new InvalidArgumentException( $name . ' must be a non-empty array' );
      
      return $this->value == $val[0];
    }
    else if ( $this->isValid( $name ))
    {
      //..By value
      return $this->value == $name;
    }
    else
      throw new InvalidArgumentException( $name . ' is not a valid method of ' . static::class );
  }
  


  /**
   * Sets the current enum value
   * @param string $p Enum value
   * @throws InvalidArgumentException if $p is not a member of this enum 
   */
  public function setValue( string $p ) : void
  {
    try {
      $value = $this->findValue( $p );      
    } catch ( InvalidArgumentException $e ) {
      throw new InvalidArgumentException( '"' . $p . '" is not a member of ' . static::class );
    }    
            
    if ( $this->value() != $value )
    {
      $old = $this->value();
      $this->value = $value;
      $this->onChange( $old, $value );
    }
  }


  private function findValue( string $value )
  {
    if ( isset( $this->constants[$value] ))
    {
      $key = $value;
      $value = $this->constants[$value];   
      if ( is_array( $value ) && !empty( $value ))
      {
        return $value[0];
      }
      else if ( is_array( $value ))
        throw new \Exception( 'Enum constant (' . $key . ') is an empty array, which is wrong.' );
    }
    else if ( isset( $this->values[$value] ))
    {
      return $value;
    }
    else 
    {
      foreach( $this->values as $k => $v )
      {
        if (( is_array( $v ) && in_array( $value, $v ))
          || $v == $value )
        {
          return $k;
        }
      }
    }
    
    throw new InvalidArgumentException( '"' . (( is_array( $value )) ? implode( ',', $value ) : '' ) . '" is not a member of ' . static::class );
  }
  

 
  
  
  /**
   * Retrieve a list of stored values 
   * @return type
   */
  public function getStoredValues()
  {
    return $this->values;
  }

  
  
  /**
   * This will check to see if the current enum value is equal to one of the values in the list.
   * @param string $values One or more potential values (NOT the constant names that would be used for isset(), use the constant values or the constants themselves )
   * @return boolean 
   */
  public function is( string ...$values ) : bool
  {
    foreach( $values as $v )
    {
      try {
        if ( $this->value == $this->findValue( $v ))
          return true;
      } catch ( InvalidArgumentException $e ) {
        //..Do nothing 
      }
    }
    
    return false;
  }
  

  /**
   * Detect if a value is a valid member of this enum.
   * @param int $v Possible enum value
   * @return bool is valid
   */
  public function isValid( string $v ) : bool
  {    
    try {
      $this->findValue( $v );      
    } catch ( InvalidArgumentException $e ) {
      return false;
    }
    
    return true;
  }

  
  /**
   * Retrieve the associated value for an enum constant.
   * @return mixed value
   */
  public function getStoredValue()
  {
    if ( isset( $this->values[$this->value] ))
      return $this->values[$this->value];

    return null;
  }  
  
  /**
   * Initialize the enum
   * @param string $init Initial enum value
   */
  private function init( string $init ) : void 
  {
    $this->constants = static::constants();
    $newEnum = [];
    
    foreach( $this->enum as $value )
    {
      if ( !is_array( $value ) || empty( $value ))
        throw new InvalidArgumentException( 'All enum members must be arrays with a minimum length of one' );
      
      $value = array_values( $value );
      
      if ( sizeof( $value ) == 2 )
        $this->values[$value[0]] = $value[1];
      else
        $this->values[$value[0]] = $value;
      
      $newEnum[] = $value[0];
    }
    
    
    $this->enum = $newEnum;

    if ( !empty( $init ))
      $this->$init;
    else if ( is_array( $this->value ) && empty( $this->value ))
      $this->value = '';
    else if ( is_array( $this->value ))
      $this->value = reset( $this->value );
    
  }  
}
