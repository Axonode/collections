<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\Hashable;

/**
 * Represents a list of unique items.
 *
 * @template T
 *
 * @implements ISet<T>
 */
final class Set implements ISet
{
    /**
     * @var array<string, int>
     */
    private array $keys = [];

    /**
     * @var list<T>
     */
    private array $values = [];

    private int $pointer = 0;

    /**
     * @param list<T> $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function current(): mixed
    {
        return $this->values[$this->pointer];
    }

    public function next(): void
    {
        $this->pointer++;
    }

    public function key(): int
    {
        return $this->pointer;
    }

    public function valid(): bool
    {
        return $this->offsetExists($this->pointer);
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('The specified key does not exist.');
        }

        return $this->values[$offset];
    }

    /**
     * @inheritdoc
     * @param int $offset
     * @param T $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset < 0 || $offset > $this->count()) {
            throw new \OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        if ($this->contains($value)) {
            if ($this->offsetGet($offset) === $value) {
                return;
            }

            throw new \InvalidArgumentException('The specified value is already present in the set.');
        }

        $internalKey = $this->toInternalKey($this->values[$offset]);
        $this->values[$offset] = $value;
        unset($this->keys[$internalKey]);
        $this->keys[$this->toInternalKey($value)] = $offset;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('The specified key does not exist.');
        }

        $this->remove($this->values[$offset]);
    }

    public function count(): int
    {
        return count($this->values);
    }

    public function contains(mixed $value): bool
    {
        return array_key_exists($this->toInternalKey($value), $this->keys);
    }

    public function add(mixed $value): void
    {
        if ($this->contains($value)) {
            return;
        }

        $internalKey = $this->toInternalKey($value);
        $this->keys[$internalKey] = count($this->values);
        $this->values[$this->keys[$internalKey]] = $value;
    }

    public function remove(mixed $value): void
    {
        if (!$this->contains($value)) {
            throw new \OutOfBoundsException('The specified value is not present in the set.');
        }

        unset($this->values[$this->keys[$this->toInternalKey($value)]]);
        $this->keys = [];
        $this->values = array_values($this->values);
        foreach ($this->values as $index => $item) {
            $this->keys[$this->toInternalKey($item)] = $index;
        }
    }

    /**
     * @param T $publicKey
     */
    private function toInternalKey(mixed $publicKey): string
    {
        return match (gettype($publicKey)) {
            'string' => $publicKey,
            'integer', 'resource', 'resource (closed)', 'double' => (string) $publicKey,
            'object' => $publicKey instanceof Hashable ? $publicKey->getHash() : spl_object_hash($publicKey),
            'NULL' => 'null',
            'array' => serialize($publicKey),
            'boolean' => $publicKey ? 'true' : 'false',
            default => throw new \InvalidArgumentException('Unsupported offset type'),
        };
    }
}
