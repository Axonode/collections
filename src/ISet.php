<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a list of unique items.
 *
 * @template T
 *
 * @extends ICollection<int, T>
 */
interface ISet extends ICollection
{
    /**
     * Determines if the given value is present in the set.
     *
     * @param T $value
     */
    public function contains(mixed $value): bool;

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
}
