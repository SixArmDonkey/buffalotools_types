<?php
/**
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 *
 * Copyright (c) 2012-2020 John Quinn <johnquinn3@gmail.com>
 * 
 * @author John Quinn
 */


class SampleEnum extends \buffalokiwi\buffalotools\types\Enum
{
  const KEY1 = 'value1';
  const KEY2 = 'value2';
  const KEY3 = 'value3';
  
  
  protected array $enum = [self::KEY1, self::KEY2, self::KEY3];
}
