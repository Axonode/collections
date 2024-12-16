<?php

declare(strict_types=1);

namespace Axonode\Collections\Contracts;

use Axonode\Collections\SortDirection;

/**
 * Represents a list of unique items. The items are indexed by sequential integers.
 *
 * @template T
 *
 * @extends ICollection<int, T>
 */
interface ISet extends ICollection
{
    /**
     * Returns a primitive representation of the set.
     *
     * @return T[]
     */
    public function toArray(): array;

    /**
     * Adds a new item to the set.
     *
     * @param T $value
     */
    public function add(mixed $value): void;

    /**
     * Removes the given value from the set.
     *
     * @param T $value
     *
     * @throws \OutOfBoundsException when the value is not present in the set.
     */
    public function remove(mixed $value): void;

    /**
     * Pushes one or more values onto the end of the set.
     *
     * @param T ...$values
     */
    public function push(mixed ...$values): void;

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
