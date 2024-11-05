<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a collection of items.
 *
 * @template TKey
 * @template TValue
 *
 * @extends \ArrayAccess<TKey, TValue>
 * @extends \Iterator<TKey, TValue>
 */
interface ICollection extends \ArrayAccess, \Iterator, \Countable
{
    /**
     * Returns a list of the keys in the collection.
     *
     * @return ISet<TKey>
     */
    public function keys(): ISet;

    /**
     * Returns a list of the values in the collection.
     *
     * @return IList<TValue>
     */
    public function values(): IList;

    /**
     * Returns a list of the items in the collection.
     *
     * @return IList<TValue>
     */
    public function toList(): IList;

    /**
     * Returns a set of the items in the collection.
     *
     * @return ISet<TValue>
     */
    public function toSet(): ISet;

    /**
     * Returns a dictionary of the items in the collection.
     *
     * @return IDictionary<TKey, TValue>
     */
    public function toDictionary(): IDictionary;

    /**
     * Applies the given $selector to all elements in the collection.
     *
     * @param callable(TValue, TKey=): TValue $selector
     *
     * @return void
     */
    public function apply(callable $selector): void;

    /**
     * Chunks the collection into lists with length elements. The last chunk may contain less than length elements.
     *
     * @param int<1, max> $length
     *
     * @return IList<$this>
     */
    public function chunk(int $length): IList;

    /**
     * Determines if the given value is present in the collection.
     *
     * @param TValue $value
     */
    public function contains(mixed $value): bool;

    /**
     * Returns a new collection containing the items that are present in the current collection but not in the other collections.
     *
     * @param ICollection<mixed, TValue> ...$collections
     *
     * @return IDictionary<TKey, TValue>
     */
    public function diff(ICollection ...$collections): IDictionary;

    /**
     * Filtering the collection by passing each element in it to the given callback. If the callback returns true, the element will be included in the resulting collection.
     *
     * @param callable(TValue, TKey=): bool $selector
     *
     * @return self<TKey, TValue>
     */
    public function filter(callable $selector): self;

    /**
     * Returns a dictionary where the keys are the values from this collection and the values are the keys.
     *
     * @return IDictionary<TValue, TKey>
     */
    public function flip(): IDictionary;

    /**
     * Returns a dictionary containing the items from this collection which are also present in all the other collections.
     *
     * @param ICollection<mixed, TValue> ...$collections
     *
     * @return IDictionary<TKey, TValue>
     */
    public function intersect(ICollection ...$collections): IDictionary;

    /**
     * Returns a new collection containing the results of applying the given callback to each element in the collection.
     *
     * @template TNewValue
     *
     * @param callable(TValue, TKey=): TNewValue $selector
     *
     * @return self<TKey, TNewValue>
     */
    public function map(callable $selector): self;

    /**
     * Merges the elements of this and the passed collections into a new dictionary. Keys are preserved regardless of their type. In case of matching keys, the latest will be used.
     *
     * @param ICollection<TKey, TValue> ...$collections
     *
     * @return IDictionary<TKey, TValue>
     */
    public function merge(ICollection ...$collections): IDictionary;

    /**
     * Removes the last element from the collection and returns it.
     *
     * @return TValue
     *
     * @throws \UnderflowException when the collection is empty
     */
    public function pop(): mixed;

    /**
     * Returns a dictionary of $count elements randomly selected from the collection.
     *
     * @param int<1, max> $count
     *
     * @return IDictionary<TKey, TValue>
     *
     * @throws \UnderflowException when the collection is empty
     * @throws \OutOfRangeException when the count is greater than the number of elements in the collection
     *
     * @note This method does not generate cryptographically secure values.
     */
    public function random(int $count = 1): IDictionary;

    /**
     * Returns a dictionary of $count elements randomly selected from the collection using a cryptographically secure random number generator.
     *
     * @param int<1, max> $count
     *
     * @return IDictionary<TKey, TValue>
     *
     * @throws \UnderflowException when the collection is empty
     * @throws \OutOfRangeException when the count is greater than the number of elements in the collection
     */
    public function secureRandom(int $count = 1): IDictionary;

    /**
     * Reduces the collection to a single value by iteratively applying a callback function to each element.
     *
     * @template TResult
     *
     * @param callable(TResult|null, TValue): (TResult|null) $selector
     * @param TResult|null $initial
     *
     * @return TResult|null
     */
    public function reduce(callable $selector, mixed $initial = null): mixed;

    /**
     * Search for the given $value in the collection and returns the first corresponding key if found.
     *
     * @param TValue $value
     *
     * @return TKey|null
     */
    public function search(mixed $value): mixed;



    /**
     * Removes the first element of the collection and returns it.
     *
     * @return TValue
     *
     * @throws \UnderflowException when the collection is empty
     */
    public function shift(): mixed;
}
