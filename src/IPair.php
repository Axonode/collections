<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a key-value pair.
 *
 * @template TKey of array-key|object
 * @template TValue
 */
interface IPair
{
    /**
     * @return TKey The key of the pair.
     */
    public function key(): mixed;

    /**
     * Creates a new pair with the specified key.
     *
     * @param TKey $key
     * @return IPair<TKey, TValue>
     */
    public function withKey(mixed $key): IPair;

    /**
     * @return TValue The value of the pair.
     */
    public function value(): mixed;

    /**
     * Creates a new pair with the specified value.
     *
     * @param TValue $value
     * @return IPair<TKey, TValue>
     */
    public function withValue(mixed $value): IPair;
}
