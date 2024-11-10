<?php

declare(strict_types=1);

namespace Axonode\Collections\Contracts;

use Axonode\Collections\SortDirection;

/**
 * Represents a list of items.
 *
 * @template T
 *
 * @extends ICollection<int, T>
 */
interface IList extends ICollection
{
    /**
     * Returns a primitive representation of the list.
     *
     * @return T[]
     */
    public function toArray(): array;

    /**
     * Returns a dictionary, where the keys are the unique values in the collection, and the values are the number of occurrences.
     *
     * @return IDictionary<T, int>
     */
    public function countValues(): IDictionary;

    /**
     * Pads the list to the size of length with the given value on the left side.
     *
     * @param int<1, max> $length
     * @param T $value
     *
     * @return $this
     *
     * @throws \OutOfRangeException when the length is less than 1.
     */
    public function padLeft(int $length, mixed $value): self;

    /**
     * Pads the list to the size of length with the given value on the right side.
     *
     * @param int<1, max> $length
     * @param T $value
     *
     * @return $this
     *
     * @throws \OutOfRangeException when the length is less than 1.
     */
    public function padRight(int $length, mixed $value): self;

    /**
     * Pushes one or more values onto the end of the list.
     *
     * @param T ...$values
     */
    public function push(mixed ...$values): void;

    /**
     * Search for all items in the list that match the given selector, and return a list of their corresponding keys.
     *
     * @param callable(T): bool $selector
     *
     * @return IList<int>
     */
    public function searchAll(callable $selector): IList;

    /**
     * Sorts the items of the collection in the given order.
     *
     * @param SortDirection $direction
     *
     * @return $this
     */
    public function sort(SortDirection $direction = SortDirection::ASCENDING): self;

    /**
     * Sorts the items of the collection by the given selector.
     *
     * @param callable(T, T): int<-1, 1> $selector
     *
     * @return $this
     */
    public function sortBy(callable $selector): self;
}
