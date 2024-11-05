<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\Hashable;

/**
 * Represents a key-value pair.
 *
 * @template TKey
 * @template TValue
 */
interface IPair extends Hashable
{
    /**
     * @return TKey The key of the pair.
     */
    public function key(): mixed;

    /**
     * Creates a new pair with the specified key.
     *
     * @template TNewKey
     *
     * @param TNewKey $key
     *
     * @return IPair<TNewKey, TValue>
     */
    public function withKey(mixed $key): IPair;

    /**
     * @return TValue The value of the pair.
     */
    public function value(): mixed;

    /**
     * Creates a new pair with the specified value.
     *
     * @template TNewValue
     *
     * @param TNewValue $value
     *
     * @return IPair<TKey, TNewValue>
     */
    public function withValue(mixed $value): IPair;
}
