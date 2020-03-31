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
use buffalokiwi\buffalotools\types\BitSet;
use buffalokiwi\buffalotools\types\IBitSet;



class BitSetTest extends AbstractBitSetTest
{
  /**
   * Create the IBitSet instance
   * @return IBitSet instance to test 
   */
  protected function createIBitSetInstance() : IBitSet
  {
    return new BitSet( 0 );
  }
}
