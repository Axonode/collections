<?php

declare(strict_types=1);

namespace Axonode\Collections;

if (!function_exists('listOf')) {
    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return ArrayList<T>
     */
    function listOf(array $items): ArrayList
    {
        return new ArrayList($items);
    }
}

if (!function_exists('setOf')) {
    /**
     * @template T
     *
     * @param T[] $items
     *
     * @return Set<T>
     */
    function setOf(array $items): Set
    {
        return new Set($items);
    }
}

if (!function_exists('dictionaryOf')) {
    /**
     * @template TKey
     * @template TValue
     *
     * @param array<TKey, TValue> $items
     *
     * @return Dictionary<TKey, TValue>
     */
    function dictionaryOf(array $items): Dictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($key, $value),
            array_keys($items),
            $items
        ));
    }
}
