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
 * A big set is a set that can handle more than 32 or 64 elements.
 * This is basically the same thing as an ISet, but without being backed by a BitSet.
 * @see ISet
 */
interface IBigSet 
{
  /**
   * Adds a member to this set 
   * @param string $name name 
   * @throws Exception if size is exceeded 
   */
  public function addMember( string ...$name ) : void;
  
  /**
   * Clear the BitSet (sets internal value to zero)
   */
  public function clear() : void;
  
  /**
   * Check to see if const is a member of this set.
   * @param string $const constant
   * @return boolean is member
   */
  public function isMember( string ...$const ) : bool;


  /**
   * This will set all of the flags to 1 in the set
   * @return ISet $this
   */
  public function setAll() : void;

  
  /**
   * Sets variables in the set to true
   * @param string $const Variables to set. 
   * @throws \InvalidArgumentException if const is not a member if the set
   */
  public function add( string ...$const ) : void;

  
  /**
   * Sets variables in the set to false
   * @param string $const members 
   * @throws \InvalidArgumentException if const is not a member if the set
   */
  public function remove( string ...$const ) : void;

  
  /**
   * Checks to see if a variable is set
   * @param string $const member to set 
   * @return boolean
   * @throws \InvalidArgumentException if const is not a member if the set
   */
  public function hasVal( string ...$const ) : bool;

  
  /**
   * Retrieves all active members in the set
   * @return array string[] active members 
   */
  public function getActiveMembers() : array;

  
  /**
   * Retrieves the list of members
   * @return array string[] all member strings 
   */
  public function getMembers() : array;

  
  /**
   * Detect if the set is empty or not.
   * Empty means there are no active bits, not no members present in the set.
   * @return boolean is empty
   */
  public function isEmpty() : bool;

  
  /**
   * Toggle bits by member 
   * @param string ...$const One or more set member names or constants 
   * @throws \InvalidArgumentException if const is not a member if the set
   */
  public function toggleMember( string ...$const ) : void;
}
