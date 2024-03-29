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

/**
 * A set used within the SetTest class.
 * Contains 3 members called KEY1 (value1), KEY2 (value2) and KEY3 (value3)
 */
class SampleSetConst extends \buffalokiwi\buffalotools\types\Set
{
  const KEY1 = 'value1';
  const KEY2 = 'value2';
  const KEY3 = 'value3';
  
  private const NOTMEMBER = 'notmember';
}
