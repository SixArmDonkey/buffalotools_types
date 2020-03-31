<?php

/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


/**
 * An enum with associated values 
 */
class ValuedEnum extends \buffalokiwi\buffalotools\types\Enum
{
  const KEY = 'key1';
  const VALUE = 'value1';
  
  const KEY2 = 'key2';
  const VALUE2 = 'value2';

  protected array $enum = [
    self::KEY => self::VALUE,
    self::KEY2 => self::VALUE2
  ];
}
