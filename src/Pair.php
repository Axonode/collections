<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a key-value pair.
 *
 * @template TKey of array-key|object
 * @template TValue
 *
 * @implements IPair<TKey, TValue>
 */
final readonly class Pair implements IPair
{
    /**
     * @param TKey $key
     * @param TValue $value
     */
    public function __construct(
        private mixed $key,
        private mixed $value
    ) {
    }

    public function key(): mixed
    {
        return $this->key;
    }

    public function withKey(mixed $key): IPair
    {
        return new self($key, $this->value);
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function withValue(mixed $value): IPair
    {
        return new self($this->key, $value);
    }
}