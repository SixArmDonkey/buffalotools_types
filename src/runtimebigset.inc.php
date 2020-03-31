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
 * A big set that can be created at runtime.
 * Go nuts.
 */
class RuntimeBigSet extends BigSet
{
  public function __construct( array $members, ...$init )
  {
    $this->members = $members;
    parent::__construct( ...$init );
  }
}
