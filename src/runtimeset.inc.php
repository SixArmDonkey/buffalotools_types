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
 * A named bit set that can be created at runtime.
 * 
 * Example:
 * 
 * Create a new Set with two bits named bit1 and bit2, and set bit1 to enabled:
 * 
 * $set = new RuntimeSet( ['bit1', 'bit2'], 'bit1' );
 * 
 * $set->isMember( 'bit1' ); //..returns true
 * $set->hasVal( 'bit1' ); //..returns true 
 */
class RuntimeSet extends Set
{
  public function __construct( array &$members, ...$init )
  {
    $this->members = $members;
    parent::__construct( ...$init );
  }
}
