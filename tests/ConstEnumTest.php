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

use buffalokiwi\buffalotools\types\IEnum;
use PHPUnit\Framework\TestCase;


class SampleConstEnum extends \buffalokiwi\buffalotools\types\Enum
{
  const KEY1 = 'key1value';
}


class ConstEnumTest extends TestCase
{
  public function testConstEnum()
  {
    $e = new SampleConstEnum();    
    $this->assertTrue( in_array( 'key1value', $e->values()));
    
    $e->KEY1;
    $this->assertSame( 'key1value', $e->value());
    
    $e->key1value;
    $this->assertSame( 'key1value', $e->value());
  }
}
