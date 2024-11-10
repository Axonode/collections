<?php

declare(strict_types=1);

namespace Axonode\Collections\Contracts;

use Axonode\Collections\SortDirection;

/**
 * @template TKey
 * @template TValue
 *
 * @extends ICollection<TKey, TValue>
 */
interface IDictionary extends ICollection
{
    /**
     * Creates a dictionary by combining the given set of keys with the list of values.
     *
     * @template TNewKey
     * @template TNewValue
     *
     * @param ISet<TNewKey> $keys
     * @param IList<TNewValue> $values
     *
     * @return IDictionary<TNewKey, TNewValue>
     *
     * @throws \ValueError if the number of keys and values do not match.
     */
    public static function combine(ISet $keys, IList $values): IDictionary;

    /**
     * Returns a dictionary, where the keys are the unique values in the collection, and the values are the number of occurrences.
     *
     * @return IDictionary<TValue, int>
     */
    public function countValues(): IDictionary;

    /**
     * Search for all items in the dictionary that match the given selector, and return a list of their corresponding keys.
     *
     * @param callable(TValue): bool $selector
     *
     * @return IList<TKey>
     */
    public function searchAll(callable $selector): IList;

    /**
     * Sorts the items of the dictionary in the given order.
     *
     * @param SortDirection $direction
     *
     * @return $this
     */
    public function sort(SortDirection $direction = SortDirection::ASCENDING): self;

    /**
     * Sorts the items of the dictionary by the given selector.
     *
     * @param callable(IPair<TKey, TValue>, IPair<TKey, TValue>): int<-1, 1> $selector
     *
     * @return $this
     */
    public function sortBy(callable $selector): self;
}
