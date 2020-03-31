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
 * An Enum that can be created on the fly.
 * 
 * Example:
 * 
 * $enum = new RuntimeEnum( ['key1', 'key2'], 'key1' );
 * 
 * $enum->value(); //..returns 'key1'
 * $enum->setValue( 'key2' ); 
 * $enum->value(); //..returns 'key2'
 * 
 * @see Enum
 */
class RuntimeEnum extends Enum
{
  /**
   * Create a new RuntimeEnum.
   * 
   * @param array $values An array or a map of enum values. 
   * ie: 
   * 
   * ['key1','key2'] is valid and will create an enum with 2 keys.
   * ['key1' => 'value1', 'key2' => 'value2'] Will create a valued enum with 2 keys and 2 values.
   * @param string $init Initial enum value.  
   * @param bool $useCache If this enum should use runtime caching should be enabled.  Use this when creating many instances of the same enum.
   * @param bool $readOnly If the enum should be read only. 
   * @param string $cacheName A unique name to use when using the runtime cache.  If omitted, a concatenated list of enum keys or values is used.
   */
  public function __construct( array $values, string $init = '', bool $useCache = true, bool $readOnly = false, string $cacheName = null )
  {
    $this->enum = $values;
    
    if ( $cacheName == null )
      $cacheName = implode( '', array_values( $values ));
    
    parent::__construct( $init, $useCache, $readOnly, $cacheName );
  }
}
