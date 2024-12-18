# Axonode Collections

## Table of contents
1. [Description](#description)
2. [Requirements](#requirements)
3. [Installation](#installation)
4. [Provided interfaces](#provided-interfaces)
5. [Usage](#usage)
6. [Contributing](#contributing)
7. [License](#license)

## Description
This library provides implementations for common data structures (Pair, List, Dictionary, Set) in PHP.

## Requirements
- PHP 8.2
- Composer
- SPL extension
- Random extension

## Installation
1. Add the package to your project using Composer:
    ```sh
    composer require axonode/collections
    ```

## Provided interfaces

For mockability, the library defines various interfaces which are implemented by concrete classes.

- `\Axonode\Collections\Object\Hashable` Represents an object that can be uniquely identified by a hash.
- `\Axonode\Collections\Contracts\IPair<TKey, TValue>` Represents an immutable key-value pair.
- `\Axonode\Collections\Contracts\ICollection<TKey, TValue>` Represents a collection of items. Parent to all collection interfaces.
- `\Axonode\Collections\Contracts\IList<T>` Represents a list of items. The items are indexed by sequential integers.
- `\Axonode\Collections\Contracts\IDictionary<TKey, TValue>` Represents a collection of items, where each item is associated with a unique key. The keys may be any of the following types: `string`, `integer`, `float`, `resource`, `object`, `boolean`, `array` or `null`.
- `\Axonode\Collections\Contracts\ISet<T>` Represents a collection of unique items. The items are indexed by sequential integers.

## Usage

### `\Axonode\Collections\Object\Hashable`

The `Hashable` interface represents an object, which can be uniquely identified by a hash. 
The hash is a string, which is used to compare objects for equality. 
If you do not want to customise how the hash is created, this package provides a trait 
(`\Axonode\Collections\Object\GeneratesObjectHash`), which implements the `getHash` method using the `spl_object_hash`
function.

### `\Axonode\Collections\Pair<TKey, TValue>`

The `Pair` class represents an immutable key-value pair.

To create a new instance use the constructor provide by the class:
```php
$pair = new \Axonode\Collections\Pair('key', 'value');
```

Pairs are immutable, so you cannot change the key or value after the object has been created, but for convenience, the 
interface provides methods to create a new pair with a different key or value:
```php
$newPair = $pair->withKey('newKey');
$newPair = $pair->withValue('newValue');
```

### Collections

All collections are derived from the interface `\Axonode\Collections\Contracts\ICollection<TKey, TValue>`.
The base `ICollection` interface extends
- `\Countable` - so you can simply count the number of elements of a collection by passing it to the `count` function
- `\ArrayAccess` - so you can access and set elements of a collection as if it were a traditional array
- `\Iterator` - so you can loop through the collection

---

#### `\Axonode\Collections\ArrayList<T>`

The `ArrayList<T>` (implementing `\Axonode\Collections\Contracts\IList<T>`) class represents a list of items. The items 
are indexed by sequential integers. To maintain the incrementing sequence of keys in the list, after any operation which 
mutates the number or order of elements in the list, the keys are reindexed.

You can get a new instance of an `ArrayList` by calling its constructor:
```php
$emptyList = new \Axonode\Collections\ArrayList();
$anotherList = new \Axonode\Collections\ArrayList(['item1', 'item2', 'item3']);
```

---

#### `\Axonode\Collections\Dictionary<TKey, TValue>`

The `Dictionary<TKey, TValue>` (implementing `\Axonode\Collections\Contracts\IDictionary<TKey, TValue>`) class represents
a collection of items, where each item is associated with a unique key. The keys usually holds a significant meaning.
Unlike a traditional array, the keys may be of any type supported by PHP. To achieve this, a dictionary is a list of 
`\Axonode\Collections\Contracts\IPair<TKey, TValue>` implementations.

You can get a new instance of a `Dictionary` by calling its constructor:
```php
// creating an empty dictionary by passing no arguments to the constructor
$emptyDictionary = new \Axonode\Collections\Dictionary();

$key1 = new stdClass();
$key2 = new stdClass();
// creating a dictionary where keys will be `stdClass` instances
$anotherDictionary = new \Axonode\Collections\Dictionary(
    new \Axonode\Collections\Pair($key1, 'value1'),
    new \Axonode\Collections\Pair($key2, 'value2'),
);

// retrieving a value by key
echo $anotherDictionary[$key1]; // 'value1'
```

---

#### `\Axonode\Collections\Set<T>`

The `Set<T>` (implementing `\Axonode\Collections\Contracts\ISet<T>`) class represents a collection of unique items.
The items are indexed by sequential integers. To maintain the incrementing sequence of keys in the list, after any 
operation which mutates the number or order of elements in the list, the keys are reindexed.

You can get a new instance of an `Set` by calling its constructor:
```php
$emptySet = new \Axonode\Collections\Set();
$anotherSet = new \Axonode\Collections\ArraySet(['item1', 'item2', 'item2']); // will result in ['item1', 'item2']
```

---

## Contributing
1. Fork the repository.
2. Create a new branch (`git checkout -b feature-branch`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature-branch`).
5. Create a new Pull Request.

## License
This project is licensed under the GPL v3 License - see the `LICENSE` file for details.