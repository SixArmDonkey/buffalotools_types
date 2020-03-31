# BuffaloKiwi Types 

An extremely useful package containing Enum, BitSet, Set and BigSet types for PHP 7.4.

MIT License

---

## Installation

```
composer require buffalokiwi/buffalotools_types
```

  
---
  
## Enum State Machine for PHP 7.4

Without extensions PHP is lacking an Enum type.  This is a fast Enum implementation that doubles as a state machine.  
  
Enum is abstract, and must be extended in order to create an enum.
RuntimeEnum may be used to create Enum objects on the fly.

Here's an example of how to create an Enum implementation.
1. Create a class that extends Enum.
2. Optionally add enum values as class constants.
3. List the enum values in a protected array property named "$enum".


```php
class EnumImpl extends Enum
{
  //..Optional class constants containing enum values 
  const KEY1 = 'key1';
  const KEY2 = 'key2';

  //..Required $enum array property containing all possible enum values.
  protected array $enum = [
    self::KEY1,
    self::KEY2
  ];

  //..Optional default value
  protected string $value = self::KEY1;

  //..Optional change event can be used
  protected function onChange( $oldVal, $newVal ) : void
  {
    //..Do something on change
  } 
}
```

Optionally, Enum can also contain additional values  

```php
class ValuedEnumImpl extends Enum
{
  //..Optional class constants containing enum values 
  const KEY1 = 'key1';
  const KEY2 = 'key2';

  //..Required $enum array property containing all possible enum values.
  protected array $enum = [
    self::KEY1 => 'stored value 1',
    self::KEY2 => 'stored value 2'
  ];

  //..Optional default value
  protected string $value = self::KEY1;

  //..Optional change event can be used
  protected function onChange( $oldVal, $newVal ) : void
  {
    //..Do something on change
  } 
}
```

### Enum Usage
 
Create enum instance and initialize to KEY1
```php
$enum = EnumImpl::KEY1();
```

Create enum instance and initialize to KEY1 
```php
$enum = new EnumImpl( EnumImpl::KEY1 );
```


Create enum instance and initialize to KEY1 
```php
$enum = new EnumImpl();
$enum->KEY1;

//..Or 
$enum->setValue( EnumImpl::KEY1 );
```


Test if an enum is equal to a certain value  
```php
if ( $enum->KEY1()) {
  // do something
}

if ( $enum->is( EnumImpl::KEY1 )) {
  // do something
}
```

**Get an enum value**

```php
$enum->value();  //..returns 'key1'

//..Casting enum to a string will return a string equal to the current enum value:
echo (string)$enum;  //..Prints 'key1'
```


**Set an enum value**

```php
$enum->KEY2;  //..The enum now has a value of "key2"
$enum->setValue( EnumImpl::KEY2 );
```

**Test if a member is valid**

```php
if ( $enum->isValid( EnumImpl::KEY2 )) {
  // this is valid
}
```

**Test if an enum is equal to another enum of the same class type**

```php
if ( $enum->equals( $enum2 )) {  
  //..$enum2 is of the same type and has the same value as $enum
}
```


**Retrieve a map of enum constants to values**

```php
//..Outputs ['KEY1' => 'key1', 'KEY2' => 'key2']
$constants = $enum->constants();
```

Retrieve a list of constants:

```php
//..Outputs: ['KEY1','KEY2']
$constants = $enum->keys();
```


**Listing all available enum values**
```php
//..Outputs: ['key1','key2'];
$values = $enum->values();
```

**Sorting a list of IEnum**
```php
usort( $enumList, function( IEnum $a, IEnum $b ) {
  return $a->compare( $b );
});
```


### Valued Enum

It's possible to attach arbitrary values to enum members.  This is accomplished by initializing the $enum array property 
as a map where the keys are the enum keys and the values are the arbitrary values.

Usage:

```php
$enum = new ValuedEnumImpl( ValuedEnumImpl::KEY1 ); //..Create a new valued enum equal to 'key1'
$enum->getStoredValue(); //..Returns 'stored value 1'

//..If you want to retrieve the enum value by stored value:
$enum->getByStoredValue( 'stored value 1' ); //..returns 'key1'


//..Retrieve a list of all stored values:
$enum->getStoredValues(); //..Returns ['stored value 1', 'stored value 2']
```


### Using the enum as a state machine

An enum is a natural fit for a state machine.  The Enum implementation includes several methods to accomplish this.
This essentially turns the enum into an indexed array with a reference to a single active value.

Get the index of an enum value:
```php
$index = $enum->indexOf( EnumImpl::KEY1 ); //..returns 0 
```

With the index value, we can move next and previous.  If next or previous is called when already at the end/beginning, then no action is taken.  

```php
$enum = new EnumImpl( EnumImpl::KEY1 );
$enum->moveNext(); //..$enum now equals 'key2'
$enum->movePrevious(); //..$enum now equals 'key1'
$enum->movePrevious(); //..$enum still equals 'key1' and no exception is thrown
```


Using the index value, we can implement greater than and less than.
```php
$enum2 = new EnumImpl( EnumImpl::KEY2 );

//..$enum has a value of 'key1'
$enum->greaterThan( $enum2 ); //..returns false.  
$enum->lessThan( $enum2 ); //..return true
```


Can compare using strings:
```php
$enum->greaterThanValue( EnumImpl::KEY2 ); //..return false
$enum->lessThanValue( EnumImpl::KEY2 ); //..returns true
```


Test if the enum changed from some value to a different value:
```php
$enum = new EnumImpl( EnumImpl::KEY1 );
$enum->changedFromTo( EnumImpl::KEY1, EnumImpl::KEY2 ); //..returns false 
$enum->setValue( EnumImpl::KEY2 );
$enum->changedFromTo( EnumImpl::KEY1, EnumImpl::KEY2 ); //..returns true 
```


If you simply want to know if the enum changed to some state at any time, then call changedTo().

```php
$enum->changedTo( EnumImpl::KEY2 ); //..Returns true 
```


Retrieve the change log.  This is a log of every change the enum went through during it's lifetime.
```php
$enum->getChanges(); //..Returns [['key1' => 'key2']] when using above example
```


### Enum Events

Change events can be attached to the enum object or added to a class that descends from Enum.

```php
$enum = new EnumImpl( EnumImpl::KEY1 );

//..Add a change event.  Multiple events can be added.
$enum->setOnChange( function( IEnum $enum, string $old, string $newVal ) : void {
  //..Do something on change.

  //..Optionally, throw any exception to roll back the change.  
  //..The change log will not list failed changes.
  throw new \Exception( 'No change for you' );
});

try {
  //..An exception will be thrown here due to the change event.
  $enum->setValue( EnumImpl::KEY2 );
} catch( \Exception $e ) {
  //..Do nothing
}

$enum->value(); //..This will output 'key1' since the change event throws an exception.
```

If the enum implementation overrides onChange(), the same rules as above are followed.  Throw an exception to roll back.



### Creating Enum at runtime.  

This can be accomplished by using the RuntimeEnum class.

For example, say we wanted to create an enum with two possible values, and set the initial value.  We can do the following:

//..Create a new enum with two values and initialize to 'key1'
```php
$enum = new RuntimeEnum( ['key1', 'key2'], 'key1' );
 
$enum->value(); //..returns 'key1'

//..Change the value 
$enum->setValue( 'key2' ); 
$enum->value(); //..returns 'key2'
```


---


## BitSet

BitSet is a a wrapper for a single integer, which can then be used as 32 or 64 individual boolean values, and contains methods for operating on the bits.


Create a new and empty BitSet.
This will have either 32 or 64 possible values based on the architecture the installed PHP version.

```php
$b = new BitSet( 0 );  
```


**Enable bits**
```php
$b = new BitSet( 0 );
$b->enable( 0x2 ); //..Bit 2 is now enabled 
$b->enableAt( 2 ); //..Bit 2 is enabled 
$b->setValue( 2 ); //..Bit 2 is enabled (This is the sum of all enabled bits)
```


**Disable bits**
```php
$b->disable( 0x2 ); //..Bit 2 is now disabled 
$b->disableAt( 2 ); //..Bit 2 disabled
$b->setValue( 0 ); //..All bits are disabled 
```


**Toggle bits**
```php
$b->enable( 0x2 ); //..Enable bit 2
$b->toggle( 0x2 ); //..Bit 2 is now disabled 
```


**Testing if bits are enabled**
```php
$b->enable( 0x2 );
$b->isEnabled( 0x2 ); //..Returns true
$b->isEnabledAt( 2 ); //..Returns true 
```

**Retrieving the internal value**
The internal value is the sum of all enabled bits  

```php
$b->clear(); //..Disables all bits 
$b->getValue(); //.Returns zero
$b->enable( 0x1 ); //..Enable bit 1
$b->getValue(); //..Returns one
$b->enable( 0x2 ); //..Enable bit 2
$b->getValue(); //..Returns three
$b->disable( 0x1 ); //..Disable bit 1
$b->getValue(); //..Returns two 
```

---


## Set

While bit sets are useful, wouldn't it be great if we could name the bits?  If you like naming things, and you want all your bits to have names, then
look no further than the Set class!

The Set implementation is a BitSet with named bits.  

Originally, this was designed to work with set column types in MySQL, and it can still be used for that! 

Here's how it works:

1. Create a new class that descends from Set 
2. Add class constants for each bit and set the value equal to some string value.
3. Add the constants to a protected array property named $members.

```php
class SetImpl 
{
  const BIT1 = 'bit1';
  const BIT2 = 'bit2';

  protected array $members = [
    self::BIT1,
    self::BIT2
  ];
}
```

**Creating the Set instance**

Create a new set and enable zero bits
```php
$set = new SetImpl();
```

Create a new set and enable both bits
```php
$set = new SetImpl( SetImpl::BIT1, 'bit2' );
```

Create a new set and enable both bits
```php
$set = new SetImpl( ['bit1', 'bit2'] );
```


**Enabling bits**

```php
$set = new SetImpl();

$set->add( SetImpl::BIT1 ); //..Use the add method
$set->add( 0x1 ); //..Add method also accepts integers 
$set->BIT1 = true; //..Use magic.  The key is the class constant.  
$set->bit1 = true; //..Using magic, but with the bit's name and not the class constant 
```


**Disabling bits**
```php
$set = new SetImpl();
$set->remove( SetImpl::BIT1 ); 
$set->remove( 0x1 );
$set->BIT1 = false;
$set->bit2 = false;
```


**Retrieve the bit value**
```php
$set = new SetImpl();
$set->BIT1; //..returns 1
$set->bit1; //..returns 1
```

#### Testing bits

**Test if all specified bits are valid members of the set**
```php
$set = new SetImpl();
$set->isMember( 'bit1' ); //..returns true 
$set->isMember( 'bit1', 'bit2' ); //..returns true
```

**Test if a bit is enabled**
```php
$set = new SetImpl();
$set->add( 'bit1' ); 
$set->hasVal( 'bit1' ); //..Returns true
$set->hasVal( 'bit1', 'bit2' ); //..Returns false 
```


**Test if the set has zero bits enabled**
```php
$set = new SetImpl();
$set->isEmpty(); //..Returns true 
```

**Test if one or more values are enabled**
```php
$set = new SetImpl( 'bit1' );
$set->hasAny( 'bit1', 'bit2' ); //..Returns true since bit1 is enabled 
```


**Adding new Set members at runtime** 

Say you had a set with 2 members, and you forgot you really wanted 3.  No problem!  We can do crazy things like this:

```php
$set = new SetImpl();
$set->addMember( 'bit3' ); //..Add a new member to the bit set.
$set->isMember( 'bit3' ); //..Returns true 
$set->bit3; //..returns 0x3 
```


**Retrieving a list of the names of all available bits**
```php
$set = new SetImpl();
$set->getMembers(); //..returns ['bit1', 'bit2'];
```


**Retrieving a list of all enabled bit names**
```php
$set = new SetImpl();
$set->add( 'bit1' );
$set->getActiveMembers();  //..Returns ['bit1'];
```

If you want to retrieve the total value of the set (an integer representing all possible bits), you can call getTotal().
```php
$set = new SetImpl();
$set->getTotal(); //..Returns 3
```


If you want to create a Set at runtime, and don't want to create a class, then use RuntimeSet.

Example:

Create a new Set with two bits named bit1 and bit2, and set bit1 to enabled:
```php
$set = new RuntimeSet( ['bit1', 'bit2'], 'bit1' );

$set->isMember( 'bit1' ); //..returns true
$set->hasVal( 'bit1' ); //..returns true 
```


It's also possible to add a value for each bit in a Set by using the MapSet class.
This adds the get() method to ISet, and can be used to retrieve the value attached to a named bit.

Usage:

```php
class MapSetImpl extends MapSet
{
  protected array $members = [
    'bit1' => 'value1',
    'bit2' => 'value2'
  ];
}

$set = new MapSetImpl();
$set->get( 'bit1' ); //..returns 'value1'
```

---

## BigSet

A big set is a set that can handle more than 32 or 64 elements, but loses the ability to perform bitwise operations since the 
set is no longer backed by a single integer.

This is basically the same thing as an ISet, but without being backed by a BitSet.

Internally, this maintains a list of ISet instances and each member of the big set is mapped to a bit one of the internal sets. 
 
