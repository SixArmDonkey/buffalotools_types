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


class SetDecorator extends BitSetDecorator implements ISet
{
  private ISet $set;
  
  public function __construct( ISet $set )
  {
    parent::__construct( $set );
    $this->set = $set;
  }
  
  /**
   * Adds a member to this set 
   * @param string $name name 
   * @throws Exception if size is exceeded 
   */
  public function addMember( string $name ) : void
  {
    $this->set->addMember( $name );
  }
  
  
  /**
   * Check to see if const is a member of this set.
   * @param string $const constant
   * @return boolean is member
   */
  public function isMember( string ...$const ) : bool
  {
    return $this->set->isMember( ...$const );
  }


  /**
   * This will set all of the flags to 1 in the set
   * @return ISet $this
   */
  public function setAll() : void
  {
    $this->set->setAll();
  }

  
  /**
   * Sets variables in the set to true
   * @param string $const Variables to set. 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function add( string ...$const ) : void
  {
    $this->set->add( ...$const );
  }

  
  /**
   * Sets variables in the set to false
   * @param string $const members 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function remove( string ...$const ) : void
  {
    $this->set->remove( ...$const );
  }

  
  /**
   * Checks to see if a variable is set
   * @param string $const member to set 
   * @return boolean
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function hasVal( string ...$const ) : bool
  {
    return $this->set->hasVal( ...$const );
  }

  
  /**
   * Retrieves all active members in the set
   * @return array string[] active members 
   */
  public function getActiveMembers() : array
  {
    return $this->set->getActiveMembers();
  }

  
  /**
   * Retrieves the list of members
   * @return array string[] all member strings 
   */
  public function getMembers() : array
  {
    return $this->set->getMembers();
  }

  
  /**
   * Retrieve the Integer value of the set.
   * This is the sum of the values of each member.
   * @return int Set value
   */
  public function getTotal() : int
  {
    return $this->set->getTotal();
  }

  
  /**
   * Detect if the set is empty or not.
   * Empty means there are no active bits, not no members present in the set.
   * @return boolean is empty
   */
  public function isEmpty() : bool
  {
    $this->set->isEmpty();
  }
  
  
  /**
   * Toggle bits by member 
   * @param string ...$const One or more set member names or constants 
   * @throws InvalidArgumentException if const is not a member if the set
   */
  public function toggleMember( string ...$const ) : void
  {
    $this->set->toggleMember( ...$const );
  }
  
  
  /**
   * Test if any of the values are set.
   * @param string $const list of values 
   * @return bool set 
   */
  public function hasAny( string ...$const ) : bool
  {
    return $this->set->hasAny( ...$const );
  }
}
