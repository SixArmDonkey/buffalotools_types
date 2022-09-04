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
use JsonSerializable;
use ReflectionClass;



/**
 * Enum and State Machine for PHP 7.4
 * 
 * This class must be extended in order to create an enum.
 * RuntimeEnum may be used to create Enum objects on the fly.
 * 
 * This is an example of a Enum implementation.
 * Create a class that extends Enum.
 * Optionally add enum values as class constants.
 * List the enum values in a protected array property named "$enum".
 * 
 * class EnumImpl extends Enum
 * {
 *   //..Optional class constants containing enum values 
 *   const KEY1 = 'key1';
 *   const KEY2 = 'key2';
 * 
 *   //..Required $enum array property containing all possible enum values.
 *   protected array $enum = [
 *     self::KEY1,
 *     self::KEY2
 *   ];
 * 
 *   //..Optional default value
 *   protected string $value = self::KEY1;
 * 
 *   //..Optional change event can be used
 *   protected function onChange( $oldVal, $newVal ) : void
 *   {
 *     //..Do something on change
 *   } 
 * }
 * 
 * //..Optionally, Enum can also contain additional values
 * class ValuedEnumImpl extends Enum
 * {
 *   //..Optional class constants containing enum values 
 *   const KEY1 = 'key1';
 *   const KEY2 = 'key2';
 * 
 *   //..Required $enum array property containing all possible enum values.
 *   protected array $enum = [
 *     self::KEY1 => 'stored value 1',
 *     self::KEY2 => 'stored value 2'
 *   ];
 * 
 *   //..Optional default value
 *   protected string $value = self::KEY1;
 * 
 *   //..Optional change event can be used
 *   protected function onChange( $oldVal, $newVal ) : void
 *   {
 *     //..Do something on change
 *   } 
 * }
 * 
 * 
 * Use like this:
 * 
 * //..Create enum instance and initialize to KEY1
 * $enum = EnumImpl::KEY1();
 * 
 * //..Create enum instance and initialize to KEY1 
 * $enum = new EnumImpl( EnumImpl::KEY1 );
 * 
 * //..Create enum instance and initialize to KEY1 
 * $enum = new EnumImpl();
 * $enum->KEY1;
 * //..Or 
 * $enum->setValue( EnumImpl::KEY1 );
 * 
 * //..Test if an enum is equal to a certain value  
 * if ( $enum->KEY1()) {
 *   // do something
 * }
 * 
 * if ( $enum->is( EnumImpl::KEY1 )) {
 *   // do something
 * }
 * 
 * //..Get an enum value
 * $enum->value();  //..returns 'key1'
 * 
 * //..Casting enum to a string will return a string equal to the current enum value:
 * echo (string)$enum;  //..Prints 'key1'
 * 
 * 
 * //..Set an enum value:
 * 
 * $enum->KEY2;  //..The enum now has a value of "key2"
 * $enum->setValue( EnumImpl::KEY2 );
 * 
 * 
 * 
 * 
 * //..Test if a member is valid
 * if ( $enum->isValid( EnumImpl::KEY2 )) {
 *   // this is valid
 * }
 * 
 * 
 * //..Test if an enum is equal to another enum of the same class type:
 * if ( $enum->equals( $enum2 )) {  
 *   //..$enum2 is of the same type and has the same value as $enum
 * }
 * 
 * 
 * //..Retrieve a map of enum constants to values 
 * //..Outputs ['KEY1' => 'key1', 'KEY2' => 'key2']
 * $constants = $enum->constants();
 * 
 * 
 * //..Retrieve a list of constants:
 * //..Outputs: ['KEY1','KEY2']
 * $constants = $enum->keys();
 * 
 * 
 * //..Listing all available enum values:
 * //..Outputs: ['key1','key2'];
 * $values = $enum->values();
 * 
 * 
 * //..Sorting a list of IEnum:
 * usort( $enumList, function( IEnum $a, IEnum $b ) {
 *   return $a->compare( $b );
 * });
 * 
 * 
 * 
 * //..When using valued enums, here's how to access the stored values:
 * 
 * $enum = new ValuedEnumImpl( ValuedEnumImpl::KEY1 ); //..Create a new valued enum equal to 'key1'
 * $enum->getStoredValue(); //..Returns 'stored value 1'
 * 
 * //..If you want to retrieve the enum value by stored value:
 * $enum->getByStoredValue( 'stored value 1' ); //..returns 'key1'
 * 
 * 
 * //..Retrieve a list of all stored values:
 * $enum->getStoredValues(); //..Returns ['stored value 1', 'stored value 2']
 * 
 * 
 * 
 * 
 * //..Using the enum as a state machine
 * 
 * //..Get the index of an enum value:
 * $index = $enum->indexOf( EnumImpl::KEY1 ); //..returns 0 
 * 
 * //..With the index value, we can move next and previous.  If next or previous is called when already at the end/beginning, then no action is taken.
 * $enum = new EnumImpl( EnumImpl::KEY1 );
 * $enum->moveNext(); //..$enum now equals 'key2'
 * $enum->movePrevious(); //..$enum now equals 'key1'
 * $enum->movePrevious(); //..$enum still equals 'key1' and no exception is thrown
 * 
 * 
 * 
 * //..Using the index value, we can implement greater than and less than.
 * $enum2 = new EnumImpl( EnumImpl::KEY2 );
 * 
 * //..$enum has a value of 'key1'
 * $enum->greaterThan( $enum2 ); //..returns false.  
 * $enum->lessThan( $enum2 ); //..return true
 * 
 * //..Can compare using strings:
 * $enum->greaterThanValue( EnumImpl::KEY2 ); //..return false
 * $enum->lessThanValue( EnumImpl::KEY2 ); //..returns true
 * 
 * //..Test if the enum changed from some value to a different value:
 * 
 * $enum = new EnumImpl( EnumImpl::KEY1 );
 * $enum->changedFromTo( EnumImpl::KEY1, EnumImpl::KEY2 ); //..returns false 
 * $enum->setValue( EnumImpl::KEY2 );
 * $enum->changedFromTo( EnumImpl::KEY1, EnumImpl::KEY2 ); //..returns true 
 * 
 * //..If you simply want to know if the enum changed to some state at any time, then call changedTo().
 * $enum->changedTo( EnumImpl::KEY2 ); //..Returns true 
 * 
 * //..Retrieve the change log.  This is a log of every change the enum went through during it's lifetime.
 * $enum->getChanges(); //..Returns [['key1' => 'key2']] when using above example
 * 
 * 
 * 
 * //..Enum Events:
 * 
 * Change events can be attached to the enum object.
 * $enum = new EnumImpl( EnumImpl::KEY1 );
 * 
 * //..Add a change event.  Multiple events can be added.
 * $enum->setOnChange( function( IEnum $enum, string $old, string $newVal ) : void {
 *   //..Do something on change.
 * 
 *   //..Optionally, throw any exception to roll back the change.  
 *   //..The change log will not list failed changes.
 *   throw new \Exception( 'No change for you' );
 * });
 * 
 * try {
 *   //..An exception will be thrown here due to the change event.
 *   $enum->setValue( EnumImpl::KEY2 );
 * } catch( \Exception $e ) {
 *   //..Do nothing
 * }
 * 
 * $enum->value(); //..Using the above example, this will output 'key1' since the change event throws an exception.
 * 
 * If the enum implementation overrides onChange(), the same rules as above are followed.  Throw an exception to roll back.
 * 
 */
class Enum implements IEnum
{
  /**
   * A map of class name to values array 
   * @var array 
   */
  private static $RUNTIME = [];
  
  /**
   * A list of values in this enum
   * Override this in a decending class
   * @var array
   */
  protected array $enum = [];

  /**
   * Current enum value
   * Override this in a descending class and set a default value if desired
   * @var string
   */
  protected string $value = '';

  /**
   * A list of values for the enum
   * @var array
   */
  private array $values = [];

  /**
   * True means exceptions are thrown if the value wants to change
   * @var bool
   */
  private bool $readOnly;

  /**
   * Used as part of the readOnly flag, and makes setting an initial
   * value possible.
   * @var bool
   */
  private bool $hasSetValue = false;
  
  /**
   * A list of constants 
   * @var string[]
   */
  private array $constants = [];
  
  
  /**
   * A map of enum values to true for testing values.
   * @var array [key => true]
   */
  private array $evals = [];
  
  /**
   * A list of status changes that have occurred during this object's lifetime.
   * @var array [from => [to]]
   */
  private array $changeLog = [];
  
  private array $changes = [];
  
  /**
   * Change event 
   * @var \Closure[]
   */
  private array $onChange = [];

  
  /**
   * Create a new Enum instance 
   * @param string $init Initial enum value.  
   * @param bool $useCache If this enum should use runtime caching should be enabled.  Use this when creating many instances of the same enum.
   * @param bool $readOnly If the enum should be read only. 
   * @param string $cacheName A unique name to use when using the runtime cache.  If omitted, a concatenated list of enum keys or values is used.
   */
  public function __construct( string $init = '', bool $useCache = true, bool $readOnly = false, string $cacheName = null )
  {
    $this->readOnly = $readOnly;
    
    $cls = ( $cacheName != null ) ? $cacheName : static::class;
    
    if ( $useCache && isset( self::$RUNTIME[$cls] ))
    {
      $this->enum   =& self::$RUNTIME[$cls][0];
      $this->values =& self::$RUNTIME[$cls][1];
      $this->value  = self::$RUNTIME[$cls][2];
      $this->constants =& self::$RUNTIME[$cls][3];
      $this->evals =& self::$RUNTIME[$cls][4];
      
      if ( !empty( $init ))
      {
        if ( isset( $this->constants[$init] ))
          $this->value = $this->constants[$init];
        else
          $this->value = $init;
      }
    }
    else
    {
      $this->init( $init );
      self::$RUNTIME[$cls][0] = $this->enum;
      self::$RUNTIME[$cls][1] = $this->values;
      self::$RUNTIME[$cls][2] = $this->value;
      self::$RUNTIME[$cls][3] = $this->constants;
      self::$RUNTIME[$cls][4] = $this->evals;
    }
  }

  
  /**
   * Retrieve a map of class constant to value.
   * @return array constants 
   * @static
   */
  public static function constants() : array
  {
    $out = [];
    
    foreach(( new ReflectionClass( static::class ))->getReflectionConstants() as $c )
    {
      /* @var $c \ReflectionClassConstant */
      if ( !$c->isPublic())
        continue;
      
      $out[$c->getName()] = $c->getValue();
    }
    
    return $out;    
  }
  
  
  /**
   * Retrieve the defined class constant keys.
   * Depending on how the enum was constructed, there may or may not be class constants.
   * @return array keys 
   * @static 
   */
  public static function keys() : array
  {
    return array_keys( static::constants());
  }
  
  
  /**
   * Retrieve the defined class constant values.
   * @return array values 
   * @static
   */
  public static function values() : array
  {
    $out = [];
    
    foreach( static::constants() as $value )
    {
      if ( is_array( $value ))
      {
        if ( empty( $value ))
          continue;
        $out[] = $value[0];
      }        
      else
        $out[] = $value;
    }
    
    return $out;
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
    $cls = static::class;
    
    if ( isset( self::$RUNTIME[$cls] ) && isset( self::$RUNTIME[$cls][3][$name] ))
    {
      $val = self::$RUNTIME[$cls][3][$name];
    }
    else if ( defined( 'static::' . $name ))
    {
      $val = constant( 'static::' . $name );
    }
    else
      throw new InvalidArgumentException( __CLASS__ . '::' . $name . ' is not a valid member of this enum' );

    return new static( $val, true, ( isset( $arguments[0] ) && is_bool( $arguments[0] ) && $arguments[0] === false ) ? false : true );
  }

  
  /**
   * Sets the current enum value.
   * This accepts a constant name or a constant value
   * @param string $p Enum value
   * @return IEnum The current instance
   * @final
   */
  public function __get( string $p ) : IEnum
  {
    if ( isset( $this->constants[$p] ))
      $this->setValue( $this->constants[$p] );
    else
      $this->setValue( $p );
    
    return $this;
  }


  /**
   * Retrieve the current value
   * @return string
   * @final
   */
  public final function __toString() : string
  {
    return $this->value();
  }


  /**
   * A way to determine if a enum is equal to a specific value.
   * ie:
   * if ( $enum->TYPE())
   *   echo 'is set';
   *
   * @param string $name Constant name
   * @param string ...$arguments function arguments
   * @return bool is set
   * @throws Exception
   */
  public function __call( string $name, array $arguments ) : bool
  {
    if ( method_exists( $this, $name ))
      $this->$name( ...$arguments );
    else
      return $this->__isset( $name );
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
    if ( isset( $this->constants[$name] ))
    {
      return $this->value == $this->constants[$name];
    }
    else if ( $this->isValid( $name ))
    {
      //..By value
      return $this->value == $name;
    }
    
    //..Maybe just return false?
    //..Triggering a user warning seems reasonable.
    trigger_error( $name . ' is not a valid method of ' . static::class, E_USER_WARNING );
    return false;
  }
  
  
  /**
   * Set the enum as read only 
   * @return void
   */
  public function lock() : void
  {
    $this->hasSetValue = true;
    $this->readOnly = true;
  }
  
  
  /**
   * Sets the on change event.  This can attach multiple events.
   * @param \Closure $onChange on change f( IEnum $enum, string $oldValue, string $newValue ) : void
   * @return void
   */
  public function setOnChange( \Closure $onChange ) : void
  {
    $this->onChange[] = $onChange;
  }
  
  
  /**
   * Tests that two enum objects have an equal type and value.
   * This uses a strict comparison on the values.
   * @param \buffalokiwi\buffalotools\types\IEnum $that Test against this enum.
   * @return bool
   */
  public function equals( IEnum $that ) : bool
  {
    return $this->value() === $that->value()
      && static::class == get_class( $that );
  }
  
  
  /**
   * Comparison function.
   * 
   * Sorts by index value.
   * 
   * @param \buffalokiwi\buffalotools\types\IEnum $that Compare to that 
   * @return int -1,0,1
   * @throws InvalidArgumentException
   */
  public function compare( IEnum $that ) : int
  {
    if ( static::class != get_class( $that ))
      throw new \InvalidArgumentException( 'Supplied IEnum instance is not an instance of ' . static::class . '.  got ' . get_class( $that ));
    
    return $this->indexOf( $this->value ) <=> $that->indexOf( $that->value());
  }
  
  
  /**
   * Compare function.
   * Compares enum values.
   * @param string $that compare to 
   * @return int -1,0,1
   */
  public function compareValues( string $that ) : int
  {
    return $this->indexOf( $this->value()) <=> $this->indexOf( $that );
  }
  
  
  public function lessThanValue( string $that ) : bool
  {
    return $this->indexOf( $this->value()) < $this->indexOf( $that );
  }
  
  
  public function greaterThanValue( string $that ) : bool
  {
    return $this->indexOf( $this->value()) > $this->indexOf( $that );
  }
  
  
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
   * @param \buffalokiwi\buffalotools\types\IEnum $that
   * @return bool
   */
  public function lessThan( IEnum $that ) : bool
  {
    return $this->indexOf( $this->value ) < $that->indexOf( $that->value());
  }
  
  
  /**
   * Compare the selected index value of 2 enums of the same type and test
   * that the current index is greater than the supplied enum index.
   * @param \buffalokiwi\buffalotools\types\IEnum $that
   * @return bool
   */
  public function greaterThan( IEnum $that ) : bool
  {
    return $this->indexOf( $this->value ) > $that->indexOf( $that->value());
  }
  
  
  /**
   * Retrieve the index value of the supplied enum value.
   * @param string $value Value or constant to test
   * @return int index or -1 
   */
  public function indexOf( string $p ) : int
  {
    $val = ( isset( $this->constants[$p] )) ? $this->constants[$p] : $p;
    
    $res = array_search( $val, $this->enum, true );
    if ( $res === false )
      return -1;
    return $res;
  }  

  
  /**
   * Sets the current enum value
   * @param string $p Enum value
   * @throws InvalidArgumentException if $p is not a member of this enum 
   */
  public function setValue( string $p ) : void
  {
    if ( $this->hasSetValue && $this->readOnly )
      throw new \Exception( static::class . ' is read only and cannot be changed' );
    
    if ( !$this->isValid( $p ))
    {
      throw new InvalidArgumentException( '"' . $p . '" is not a member of ' . static::class );
    }
    
    $this->hasSetValue = true;
    
    if ( $this->value != $p )
    {
      $old = $this->value;
      if ( is_array( $this->value ))
        $old = reset( $this->value );
      
      if ( is_array( $p ))
        $p = reset( $p );
      
      $this->value = $p;
      
      $this->changeLog[$old][$p] = true;
      
      //..Allow the change event to throw exceptions and cause a rollback.
      try {
        $this->_onChange( $old, $p );
      } catch( \Exception $e ) {
        $this->value = $old;
        
        if ( isset( $this->changeLog[$old][$p] ))
        {
          unset( $this->changeLog[$old][$p] );
        }
        
        throw $e;
      }
      
      $this->changes[] = $p;      
    }
  }


  /**
   * Retrieve the value of this enum
   * @return string value
   */
  public function value() : string
  {
    return $this->value;
  }
  
  
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
  public function changedFromTo( string $from, string $to, bool $strict = false ) : bool 
  {
    if ( $strict )
      return isset( $this->changeLog[$from] ) && isset( $this->changeLog[$from][$to] );
    
    return isset( $this->changeLog[$from] ) && in_array( $to, $this->changes );
  }
  
  
  /**
   * If the order status moved into this state during the objects lifetime.
   * @param string $to Moved to this 
   * @return bool Did it move?
   */
  public function changedTo( string $to ) : bool
  {
    return in_array( $to, $this->changes );
  }
  
  
  /**
   * Retrieve a list of all changes to this enum.
   * @return array changes [from] => to
   */
  public function getChanges() : array
  {
    return $this->changes;
  }
  
  
  /**
   * This will check to see if the current enum value is equal to one of the values in the list.
   * @param string $values One or more potential values (NOT the constant names that would be used for isset(), use the constant values or the constants themselves )
   * @return boolean 
   */
  public function is( string ...$values ) : bool
  {
    foreach( $values as $val )
    {
      if ( $this->value == $val )
        return true;
    }
    
    return false;
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
   * Retrieve a list of stored values 
   * @return type
   */
  public function getStoredValues()
  {
    return $this->values;
  }
  
  
  /**
   * Retrieve the enum value by a stored value.
   * @param mixed $value value 
   * @return string enum value 
   */
  public function getByStoredValue( $value ) : string
  {
    foreach( $this->values as $enum => $stored )
    {
      if ( $stored == $value )
        return (string)$enum;
    }
    
    throw new InvalidArgumentException( 'Supplied value is not a member of ' . static::class );
  }


  /**
   * Retrieve the list of valid values
   * @return array string[] enum values 
   */
  public function getEnumValues() : array 
  {
    return $this->enum;
  }


  /**
   * Detect if a value is a valid member of this enum.
   * @param int $v Possible enum value
   * @return bool is valid
   */
  public function isValid( string $v ) : bool
  {    
    return isset( $this->evals[$v] );
  }
  
  
	/**
	 * Specify data which should be serialized to JSON
	 * <p>Serializes the object to a value that can be serialized natively by <code>json_encode()</code>.</p>
	 * @return mixed <p>Returns data which can be serialized by <code>json_encode()</code>, which is a value of any type other than a <code>resource</code>.</p>
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @since PHP 5 >= 5.4.0, PHP 7
	 */
	public function jsonSerialize()
  {
    return $this->value();
  }
  
  
  /**
   * Set the enum value to the next available state in the list.
   * If no additional values are available, this does nothing and returns
   * the current value.
   * @return string new value 
   */
  public function moveNext() : string
  {
    $v = ( is_array( $this->value )) ? reset( $this->value ) : $this->value;
    $cur = $this->indexOf( $v );
    if ( isset( $this->enum[$cur +1] ))    
      $this->setValue( $this->enum[$cur + 1] );
    return $this->value();
  }
  
  
  /**
   * Set the enum value to the previous state in the list.
   * If no additional values are available, this does nothing and returns
   * the first value in the enum list..
   * @return string new value 
   */
  public function movePrevious() : string
  {
    $v = ( is_array( $this->value )) ? reset( $this->value ) : $this->value;
    $cur = $this->indexOf( $v );
    if ( $cur > -1 && isset( $this->enum[$cur - 1] ))    
      $this->setValue( $this->enum[$cur - 1] );
    return $this->value();    
  }
  
  
  /**
   * A callback that is called when the value of the enum changes.
   * @param string|array $oldVal Old value 
   * @param string|array $newVal New Value 
   */
  protected function onChange( $oldVal, $newVal ) : void
  { 
    //..Do nothing.  Override this if desired 
  }

  
  /**
   * Calls attached functional change event, and also the protected onChange 
   * method.
   * @param string|array $oldVal Old value 
   * @param string|array $newVal new value 
   * @return void
   */
  private function _onChange( $oldVal, $newVal ) : void
  {
    //..This needs to be first.
    $this->onChange( $oldVal, $newVal );
    
    //..Then things from the outside.
    foreach( $this->onChange as $f )
    {
      $f( $this, $oldVal, $newVal );
    }        
  }
  

  /**
   * Initialize the enum
   * @param string $init Initial enum value
   */
  private function init( string $init ) : void 
  {
    $this->constants = static::constants();
    
    if ( empty( $this->enum ))
    {
      $this->enum = array_values( $this->constants );
    }
    
    $keys = array_keys( $this->enum );
    
    
    
    
    $allInt = true;
    for ( $i = 0; $i < sizeof( $keys ); $i++ )
    {
      if ( !is_int( $keys[$i] ))
      {
        $allInt = false;
        break;
      }
    }

    if ( !$allInt )
    {
      //..This has values.
      $this->values = $this->enum;      
      $this->enum = $keys;
    }
    
    foreach( $this->enum as $v )
    {
      $this->evals[$v] = true;
    }
    
    
    if ( !empty( $init ))
    {
      $this->hasSetValue = false;
      $this->__get( $init );
    }
  }
}
