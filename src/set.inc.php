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
use ReflectionClass;


/**
 * The Set implementation is a BitSet with named bits.  
 * 
 * Originally, this was designed to work with set column types in MySQL, and it can still be used for that! 
 * 
 * Here's how it works:
 * 
 * 1) Create a new class that descends from Set 
 * 2) Add class constants for each bit and set the value equal to some string value.
 * 3) Add the constants to a protected array property named $members.
 * 
 * 
 * class SetImpl 
 * {
 *   const BIT1 = 'bit1';
 *   const BIT2 = 'bit2';
 * 
 *   protected array $members = [
 *     self::BIT1,
 *     self::BIT2
 *   ];
 * }
 * 
 * 
 * Creating the Set instance
 * 
 * Create a new set and enable zero bits:
 * $set = new SetImpl();
 * 
 * Create a new set and enable both bits
 * $set = new SetImpl( SetImpl::BIT1, 'bit2' );
 * 
 * Create a new set and enable both bits
 * $set = new SetImpl( ['bit1', 'bit2'] );
 * 
 * 
 * Enabling bits:
 * 
 * $set = new SetImpl();
 * 
 * $set->add( SetImpl::BIT1 ); //..Use the add method
 * $set->add( 0x1 ); //..Add method also accepts integers 
 * $set->BIT1 = true; //..Use magic.  The key is the class constant.  
 * $set->bit1 = true; //..Using magic, but with the bit's name and not the class constant 
 * 
 * 
 * Disabling bits:
 * 
 * $set->remove( SetImpl::BIT1 ); 
 * $set->remove( 0x1 );
 * $set->BIT1 = false;
 * $set->bit2 = false;
 * 
 * Retrieve the bit value:
 * $set->BIT1; //..returns 1
 * $set->bit1; //..returns 1
 * 
 * 
 * Testing bits:
 * 
 * Test if all specified bits are valid members of the set 
 * $set->isMember( 'bit1' ); //..returns true 
 * $set->isMember( 'bit1', 'bit2' ); //..returns true
 * 
 * Test if a bit is enabled
 * $set->add( 'bit1' ); 
 * $set->hasVal( 'bit1' ); //..Returns true
 * $set->hasVal( 'bit1', 'bit2' ); //..Returns false 
 * 
 * Test if the set has zero bits enabled:
 * $set = new SetImpl();
 * $set->isEmpty(); //..Returns true 
 * 
 * Test if one or more values are enabled:
 * $set = new SetImpl( 'bit1' );
 * $set->hasAny( 'bit1', 'bit2' ); //..Returns true since bit1 is enabled 
 * 
 * 
 * Adding new Set members at runtime.
 * Say you had a set with 2 members, and you forgot you really wanted 3.  No problem!  We can do crazy things like this:
 * 
 * $set = new SetImpl();
 * $set->addMember( 'bit3' ); //..Add a new member to the bit set.
 * $set->isMember( 'bit3' ); //..Returns true 
 * $set->bit3; //..returns 0x3 
 * 
 * 
 * Retrieving a list of the names of all available bits:
 * 
 * $set = new SetImpl();
 * $set->getMembers(); //..returns ['bit1', 'bit2'];
 * 
 * Retrieving a list of all enabled bit names:
 * $set = new SetImpl();
 * $set->add( 'bit1' );
 * $set->getActiveMembers();  //..Returns ['bit1'];
 * 
 * 
 * If you want to retrieve the total value of the set (an integer representing all possible bits), you can call getTotal().
 * 
 * $set = new SetImpl();
 * $set->getTotal(); //..Returns 3
 * 
 * @see BitSet 
 */
class Set extends BitSet implements ISet
{
  /**
   * This is some nonsense right here, but it makes this quite a bit faster.
   * @var array
   */
  private static $RUNTIME = [];
  
  /**
   * Variable names (declare in child class).
   * This should be a list of string constants, NOT integers.
   * @var string[]
   */
  protected array $members = [];

  /**
   * Total value of the set
   * @var int
   */
  private int $total = 0;
  
  private string $cls;
  
  private array $constants = [];
  

  /**
   * Constructor
   * @param ... accepts variable arguments 'var1','var2'... variables to set initially
   * @throws InvalidArgumentException if any constants contain characters other
   * than [a-zA-Z0-9]
   */
  public function __construct( ...$init )
  {
    parent::__construct( 0 );
    
    
    //..If the default values passed is an array, and there is only a single
    //  argument, use that as the default list.
    while (( sizeof( $init ) == 1 ) && ( is_array( $init[0] )))
    {
      $init = $init[0];
    }

    
    if ( isset( $init[0] ) && !empty( $init[0] ) && substr( $init[0], 0, 2 ) == '@@' )
    {
      $cls = substr( $init[0], 2 );
      unset( $init[0] );
    }
    else
    {
      $cls = static::class;
    }
    
    
    //..This might be the worst thing I've ever done...
    //..There's just too much code based on this class to change the arguments.
    if ( stripos( $cls, 'runtime' ) !== false )
    {
      if ( empty( $this->members ) && defined( 'static::MEMBERS' ))
        $this->members = static::MEMBERS;
      
      $cls = implode( '',  $this->members );
    }    
    
    $this->cls = $cls;
    
    
    
    if ( !isset( self::$RUNTIME[$this->cls] ))
    {
      if ( empty( $this->members ) && defined( 'static::MEMBERS' ))
        $this->members = static::MEMBERS;
      
      $this->constants = static::constants();      
      $this->initializeSet( $this->members );
    }
    else
    {
      $this->members =& self::$RUNTIME[$this->cls][0];
      $this->total =& self::$RUNTIME[$this->cls][1];
      $this->constants =& self::$RUNTIME[$this->cls][3];
      
      if ( defined( 'static::MEMBERS' ))
        $mList = static::MEMBERS;
      else
        $mList = [];
      
      foreach( $mList as $m )
      {
        if ( !isset( self::$RUNTIME[$this->cls][0][$m] ))
          $this->addMember( $m );
      }
    }    

    
    //..init the set if necessary
    foreach ( $init as $v )
    {
      foreach( explode( ',', $v ) as $v1 )
      {
        $this->add( $v1 );
      }
    }
  }
  
  
  /**
   * Retrieve a map of class constant to value.
   * @return array constants 
   * @static
   */
  public static function constants() : array
  {
    $c = [];
    //..This is marginally faster than using defined()/constant() everywhere...
    foreach( array_keys(( new ReflectionClass( static::class ))->getConstants()) as $val )
    {
      $c[$val] = constant( 'static::' . $val );
    }    
    
    return $c;
  }  
  
  
  
  private function initializeSet( array &$members )
  {
    $a = array();

    //..starting bit
    $last = 0;

    if ( sizeof( $members ) > ( PHP_INT_SIZE * 8 ) - 1 )
      throw new \Exception( "Total number of members must not exceed " . (( PHP_INT_SIZE * 8 ) - 1 ) . ' on this system. got ' . sizeof( $this->members ));

    //..loop through the members and shift some bits
    foreach ( $members as $v )
    {
      if ( !preg_match( '/^([a-zA-Z0-9_\-]+)$/', $v ))
      {
        throw new InvalidArgumentException( '"' . $v . '" is an invalid constant name and must match the pattern: /^([a-zA-Z0-9_\-]+)$/' );
      }

      if ( empty( $last ))
        $a[$v] = 1;
      else 
        $a[$v] = $last << 1;

      $last = $a[$v];

      //..add this value to the total
      $this->total += $a[$v];
    }

    //..set the members
    $this->members = $a;
            
    self::$RUNTIME[$this->cls] = [$this->members, $this->total, [], $this->constants];
  }

  
  /**
   * Adds a member to this set 
   * @param string $name name 
   * @throws Exception if size is exceeded 
   * @final 
   */
  public final function addMember( string $name ) : void
  {
    if ( sizeof( $this->members ) + 1 > ( PHP_INT_SIZE * 8 ) - 1 )
      throw new \Exception( "Total number of members must not exceed " . (( PHP_INT_SIZE * 8 ) - 1 ) . ' on this system. got ' . sizeof( $this->members ));
          
    $b = max( $this->members ) << 1;
    $this->total += $b;
    $this->members[$name] = $b;
    self::$RUNTIME[$this->cls] = [$this->members, $this->total, self::$RUNTIME[$this->cls][2], $this->constants];
  }
  
  
  /**
   * Tests to see if some set equals some other set.
   * Sets must be of the same concrete class and have active members equal to 
   * each other.
   * @param ISet $that Some other set 
   * @return bool equals 
   */
  public function equals( ISet $that ) : bool
  {
    //..This should be checking bitset total instead of imploding.  WTF Man.
    return ( get_class( $this ) == get_class( $that )
      && implode( '', $this->getActiveMembers()) == implode( '', $that->getActiveMembers()));
  }
  

  /**
   * This will set all of the flags to 1 in the set
   * @return Set this
   */
  public function setAll() : void
  {
    $this->setValue( $this->total );
  }

  
  /**
   * For some string $p, first check to see if that is a constant defined within 
   * the class, and if not check to see if it is a member name defined within the 
   * set.  If neither of those, an exception is thrown.
   * @param string $p Argument
   * @return string Members array key 
   * @throws InvalidArgumentException
   */
  protected function getKeyFromArgument( string $p ) : string
  {
    if ( isset( $this->members[$p] ))
      return $p;
    else if ( isset( $this->constants[$p] ))
      return $this->constants[$p];
    else
    {
      return $p;
    }
  }
  

  /**
   * Sets a constant on or off.
   * Simply set it to true or false
   * @param string $p Constant
   * @param boolean $v value
   */
  public function __set( string $p, bool $v ) : void
  {
    if ( $v )
      $this->add( $p );
    else
      $this->remove( $p );
  }


  /**
   * Retrieve a member value
   * @param string $p Argument
   * @return int Member value
   * @throws \InvalidArgumentException if $p is not a member of the set 
   */
  public function __get( string $p ) : int
  {
    $key = $this->getKeyFromArgument( $p );
    
    if ( isset( $this->members[$key] ))
      return $this->members[$key];
    
    throw new \InvalidArgumentException( $p . ' is not a constant defined within the current set' );
  }


  /**
   * A way to determine if a constant is enabled in the set
   * ie:
   * if ( isset( $set->TYPE ))
   *   echo 'is set';
   *
   * @param string $name Constant name
   * @return boolean is set
   * @throws \Exception if $name is invalid
   */
  public function __isset( string $name ) : bool
  {
    return $this->hasVal( $name );
  }


  /**
   * Unset a bit in this set by constant name
   * @param string $name Constant name
   * @throws \Exception if $name is invalid
   */
  public function __unset( string $name ) : void
  {
    $this->remove( $name );
  }


  /**
   * A way to determine if a constant in this set is enabled
   * ie:
   * if ( $set->TYPE())
   *   echo 'is set';
   * 
   * Invalid member names will always return false
   * @param string $name Constant name
   * @param array $arguments function arguments
   * @return boolean is set   
   */
  public function __call( string $name, array $arguments )
  {
    if ( $this->isMember( $this->getKeyFromArgument($name )))
      return $this->hasVal( $name );
    else
      return false;
  }



  /**
   * Sets variables in the set to true.
   * This accepts a varaible number of arguments (see $const param)
   * @param string $const Bits to set.  This accepts a comma-delimited list of
   * string constants from members array.  This also accepts numeric values.
   * And this also accepts IntStr instances.
   * @return $this
   * @throws InvalidArgumentException if not a member of the class
   */
  public function add( string ...$const ) : void
  {
    foreach( $const as $val )
    {
      $k = $this->getKeyFromArgument( $val );
      if ( isset( $this->members[$k] ))
        $this->enable( $this->members[$k] );
      else if ( $this->isBase2( $val )) //..Now this is rarely if ever called instead of being called for everything.
      {
        $this->enable((int)$val );
      }
      
      /*
      if ( $this->isBase2( $val )) //..I would love to get rid of this test... 
      {
        $this->enable( $val );
      }
      else 
      {
        $k = $this->getKeyFromArgument( $val );
        if ( isset( $this->members[$k] ))
          $this->enable( $this->members[$k] );
      }
      */
    }
  }


  /**
   * Check to see if const is a member of this set.
   * @param string $const constant
   * @return boolean is member
   */
  public function isMember( string ...$const ) : bool
  {
    if ( empty( $const ))
      return false;
    
    foreach( $const as $c )
    {
      if ( !isset( $this->members[$c] ))
        return false;
    }
    
    return true;
  }


  /**
   * Sets variables in the set to false
   * @param string $const Comma-delimited list of values
   * @return $this
   */
  public function remove( string ...$const ) : void
  {
    foreach( $const as $c )
    {
      if ( $this->isBase2( $c ))
        $this->disable( $c );
      else
      {
        $k = $this->getKeyFromArgument( $c );
        if ( isset( $this->members[$k] ))
          $this->disable( $this->members[$k] );
      }
    }
  }

  
  /**
   * Test if any of the values are set.
   * @param string $const list of values 
   * @return bool set 
   */
  public final function hasAny( string ...$const ) : bool
  {
    if ( empty( $const ))
      return false;
    
    $cls = $this->cls;
    $value = $this->getValue();
    
    foreach( $const as $c )
    {
      if ( isset( self::$RUNTIME[$cls] ))
      {
        if ( !isset( self::$RUNTIME[$cls][2][$c] ))
        {
          if ( $this->isBase2( $c ))
            self::$RUNTIME[$cls][2][$c] = (int)$c;
          else //..This will throw an exception if invalid             
            self::$RUNTIME[$cls][2][$c] = $this->members[$this->getKeyFromArgument( $c )];
        }      

        if (( $value & self::$RUNTIME[$cls][2][$c] ) == self::$RUNTIME[$cls][2][$c] )
          return true;
      }
      else if ( $this->isBase2( $c ) && $this->isEnabled((int)$c ))
        return true;
      else if ( $this->isEnabled( $this->members[$this->getKeyFromArgument( $c )]))
        return true;      
    }
    
    return false;
  }
  

  /**
   * Checks to see if all supplied variables are set.
   * @param string $const constant to check
   * @return boolean is enabled
   * @final
   */
  public final function hasVal( string ...$const ) : bool
  {
    if ( empty( $const ))
      return false;
    
    $cls = $this->cls;
    
    foreach( $const as $c )
    {
      if ( isset( self::$RUNTIME[$cls] ))
      {
        if ( !isset( self::$RUNTIME[$cls][2][$c] ))
        {
          if ( $this->isBase2( $c ))
            self::$RUNTIME[$cls][2][$c] = (int)$c;
          else //..This will throw an exception if invalid             
          {
            $k = $this->getKeyFromArgument( $c );
            if ( !isset( $this->members[$k] ))
              throw new InvalidArgumentException( $c . ' is not a member of this set' );
              
            
            self::$RUNTIME[$cls][2][$c] = $this->members[$k];
          }
        }

        if ( !(( $this->value & self::$RUNTIME[$cls][2][$c] ) == self::$RUNTIME[$cls][2][$c] ))
          return false;
      }
      else if ( $this->isBase2( $c ) && !$this->isEnabled((int)$c ))
        return false;
      else if ( !$this->isEnabled( $this->members[$this->getKeyFromArgument( $c )]))
        return false;      
    }
    
    
    //..This ONLY WORKS BECAUSE getKeyFromArgument() calls isMember() and that
    //..throws an exception.
    return true;
  }


  /**
   * Retrieves all active members in the set
   * @return string[] list of active bits
   */
  public function getActiveMembers() : array
  {
    $a = array();
    foreach ( $this->members as $k => $v )
    {
      //if ( $this->hasVal( $k ))
      if ( $this->isEnabled((int)$v ))
        $a[] = $k;
    }
    return $a;
  }


  /**
   * Retrieves the list of members
   * @return string[] list of constants belonging to this class
   */
  public function getMembers() : array
  {
    return array_keys( $this->members );
  }


  /**
   * Retrieve the Integer value of the set
   * @return int Set value
   */
  public function getTotal() : int
  {
    return $this->total;
  }


  /**
   * Detect if the set is empty or not
   * @return boolean is empty
   */
  public function isEmpty() : bool
  {
    return $this->getValue() == 0;
  }


  /**
   * Returns an imploded list of the set members
   * @return int
   */
  public function __toString() : string
  {
    return implode( ',', $this->getActiveMembers());
  }


  /**
   * Toggle bits by member 
   * @param string ...$const One or more set member names or constants 
   */
  public function toggleMember( string ...$const ) : void
  {
    foreach( $const as $c )
    {
      if ( $this->isBase2( $c ))
        $this->toggle((int)$c );
      else 
        $this->toggle( $this->members[$this->getKeyFromArgument( $c )] );
    }
  }
}
