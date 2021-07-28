<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <johnquinn3@gmail.com>
 *  Types
 * @author John Quinn
 */
declare( strict_types=1 );

namespace buffalokiwi\buffalotools\types;

use InvalidArgumentException;
use ReflectionClass;


/**
 * A big set is a set that can handle more than 32 or 64 elements.
 * This is basically the same thing as an ISet, but without being backed by a BitSet.
 * 
 * 
 * Internally, this maintains a list of ISet instances.  
 * 
 * While this is generally quick enough, there is room for improvement.  A proper caching scheme needs to be implemented,
 * and may be best achieved by creating a caching decorator.  Creating an automatic caching scheme in this object has 
 * proved to be unreliable at best due to not having unique cache keys.
 */
class BigSet implements IBigSet
{
  private static $ref = 0;
  
  /**
   * Variable names (declare in child class).
   * This should be a list of string constants, NOT integers.
   * @var string[];
   */
  protected array $members = [];
  
  /**
   * A list of backing sets 
   * @var ISet[]
   */
  private array $sets = [];
  
  private array $chunkIndex = [];
  
  private string $cls = '';
  
  private array $memberCache = [];

  private array $constants = [];
  
  /**
   * Retrieve a map of class constant to value.
   * @return array constants 
   * @static
   */
  public static function constants() : array
  {
    //..Runtime Big Sets will never have constants.
    if ( strpos( static::class, 'RuntimeBigSet' ) !== false )
      return [];
    
    $c = [];
    //..This is marginally faster than using defined()/constant() everywhere...
    foreach( array_keys(( new ReflectionClass( static::class ))->getConstants()) as $val )
    {
      $c[$val] = constant( 'static::' . $val );
    }    
    
    return $c;
  }
  
  
  /**
   * Constructor
   * @param ... accepts variable arguments 'var1','var2'... variables to set initially
   * @throws InvalidArgumentException if any constants contain characters other
   * than [a-zA-Z0-9]
   */
  public function __construct( ...$init )
  {    
    $this->constants = self::constants();
    //..If the default values passed is an array, and there is only a single
    //  argument, use that as the default list.
    while (( sizeof( $init ) == 1 ) && ( is_array( $init[0] )))
    {
      $init = $init[0];
    }
    
    if ( sizeof( $init ) == 1 )
      $init = explode( ',', $init[0] );    
    

    if ( empty( $this->members ) && defined( 'static::MEMBERS' ))
      $this->members = static::MEMBERS;

    if ( isset( $init[0] ) && !empty( $init[0] ) && substr( $init[0], 0, 2 ) == '@@' )
    {
      $this->cls = substr( $init[0], 2 );
      unset( $init[0] );
    }    
    else
      $this->cls = '';
    
    $this->createSets( $this->cls,  $this->members, $init );
  }
  
  
  private function createSets( $cls, array $members, array $init = [] ) : void
  {
    if ( empty( $this->sets ))
      $max = 0;
    else
      $max = max( array_keys( $this->sets ));
    
    if ( empty( $cls ))
    {
      $cls = static::class . ( self::$ref++ );
    }
    
    $initData = [];
    $memberData = [];
    
    foreach( array_chunk( $members, ( PHP_INT_SIZE * 8 ) - 1 ) as $id => &$chunk )
    {
      $id += $max + 1;
      
      $initData[$id] = array_intersect($chunk, $init );
      $memberData[$id] = $chunk;
      
      //..This tells the RuntimeSet what type of class it is implementing, and allows internal caching to work.
      
      array_unshift( $initData[$id], '@@' . $cls . $id );
      
      $set = new RuntimeSet( $chunk, ...$initData[$id] );
      foreach( $chunk as $m ) //$set->getMembers() as $m )
      {
        $this->chunkIndex[$m] = $id;
      }
      
      $this->sets[$id] = $set;
    }  
  }
  
  
  public function __clone()
  {
    foreach( $this->sets as $k => $v )
    {
      $this->sets[$k] = clone $v;
    }
  }
  

  /**
   * Adds a member to this set 
   * @param string $name name 
   * @throws Exception if size is exceeded 
   */
  public function addMember( string ...$name ) : void
  {
    $this->createSets( $this->cls, $name );
    foreach( $name as $n )
    {
      if ( !in_array( $n, $this->members ))
        $this->members[] = $n;
      
      $this->memberCache[$n] = true;
    }    
  }
  
  
  /**
   * Tests to see if some set equals some other set.
   * Sets must be of the same concrete class and have active members equal to 
   * each other.
   * @param ISet $that Some other set 
   * @return bool equals 
   */
  public function equals( IBigSet $that ) : bool
  {
    return ( get_class( $this ) == get_class( $that )
      && implode( '', $this->getActiveMembers()) == implode( '', $that->getActiveMembers()));
  }
  

  /**
   * This will set all of the flags to 1 in the set
   * @return Set this
   */
  public function setAll() : void
  {
    $this->add( ...array_keys( $this->chunkIndex ));
  }

  
  /**
   * For some string $p, first check to see if that is a constant defined within 
   * the class, and if not check to see if it is a member name defined within the 
   * set.  If neither of those, an exception is thrown.
   * @param string &$p Argument.  This is a REFERENCE.  If a class constant is passed instead of a value, this will convert that variable to the value.
   * @return ISet|null backing set    
   */
  private function getSet( string &$p ) : ?ISet
  {
    if ( isset( $this->constants[$p] ))
      $p = $this->constants[$p];
    
    if ( !isset( $this->chunkIndex[$p] ))
    {
      return null;
      //throw new \InvalidArgumentException( $p . ' is not a valid member of this set' );
    }
    
    return $this->sets[$this->chunkIndex[$p]];
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
    $set = $this->getSet( $p );
    if ( $set == null )
      throw new \InvalidArgumentException( $p . ' is not a member of ' . static::class . '(' . $this->cls . ')' );
    return $set->__get( $p );
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
   * @param string $name Constant name
   * @param array $arguments function arguments
   * @return boolean is set
   * @throws \Exception if $name is invalid
   */
  public function __call( string $name, array $arguments )
  {
    if ( isset( $this->constants[$name] ))
      $name = $this->constants[$name];
    
    if ( $this->isMember( $name ))
      return $this->hasVal( $name );
    else
      throw new \InvalidArgumentException( $name . ' is not a valid method of ' . __CLASS__ );
  }


  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void
  {
    foreach( $this->sets as $set )
    {
      $set->clear();
    }
  }
  
  

  /**
   * Sets variables in the set to true.
   * This accepts a varaible number of arguments (see $const param)
   * @param string $const Bits to set.  This accepts a comma-delimited list of
   * string constants from members array.  This also accepts numeric values.
   * And this also accepts IntStr instances.
   * @return $this
   * @throws InvalidArgumentException if a header is not a member of the class
   */
  public function add( string ...$const ) : void
  {
    foreach( $const as $val )
    {
      $set = $this->getSet( $val );
      if ( $set == null )
        throw new \InvalidArgumentException( $val . ' is not a member of ' . static::class . '(' . $this->cls . ')' );
      
      $set->add( $val );
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
    
    try {
      foreach( $const as $c )
      { 
        
        
        if ( isset( $this->constants[$c] ))
          $c = $this->constants[$c];             
        
        if ( isset( $this->memberCache[$c] ) && !$this->memberCache[$c] )
        {
          return $this->memberCache[$c];
        }
        
        
        //..This was a fairly huge gain vs an exception.
        if ( !isset( $this->chunkIndex[$c] ))
        {
          $this->memberCache[$c] = false;
          return false;
        }
        
        $set = $this->sets[$this->chunkIndex[$c]];
  
        if ( !$set->isMember( $c ))
        {
          $this->memberCache[$c] = false;
          return false;
        }
      }
    } catch( \InvalidArgumentException $e ) {
      $this->memberCache[$c] = false;
      return false;
    }
    $this->memberCache[$c] = true;
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
      $set = $this->getSet( $c );
      if ( $set == null )
        throw new \InvalidArgumentException( $c . ' is not a member of ' . static::class . '(' . $this->cls . ')' );
      
      $set->remove( $c );
    }
  }


  /**
   * Checks to see if a variable is set
   * @param string $const constant to check
   * @return boolean is enabled
   * @final
   */
  public final function hasVal( string ...$const ) : bool
  {
    if ( empty( $const ))
      return false;
    
    foreach( $const as $c )
    {
      $set = $this->getSet( $c );
      if ( $set == null )
        throw new \InvalidArgumentException( $c . ' is not a member of ' . static::class . '(' . $this->cls . ')' );
      
      if ( $set->hasVal( $c ))
      {
        return true;
      }
    }
    
    return false;
  }


  /**
   * Retrieves all active members in the set
   * @return string[] list of active bits
   */
  public function getActiveMembers() : array
  {
    $out = [];
    foreach( $this->sets as $set )
    {
      $out = array_merge( $out, $set->getActiveMembers());
    }
    
    return $out;
  }


  /**
   * Retrieves the list of members
   * @return string[] list of constants belonging to this class
   */
  public function getMembers() : array
  {
    return array_keys( $this->chunkIndex );
  }


  /**
   * Detect if the set is empty or not
   * @return boolean is empty
   */
  public function isEmpty() : bool
  {
    foreach( $this->sets as $set )
    {
      if ( $set->getValue() > 0 )
        return false;
    }
    
    return true;
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
      $set = $this->getSet( $c );
      if ( $set == null )
        throw new \InvalidArgumentException( $c . ' is not a member of ' . static::class . '(' . $this->cls . ')' );
      
      $set->toggle( $c );
    }
  }
}
