<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\Hashable;

/**
 * Represents a collection of key-value pairs.
 *
 * @template TKey of array-key|object
 * @template TValue
 *
 * @implements IDictionary<TKey, TValue>
 */
final class Dictionary implements IDictionary
{
    /**
     * @var array<string, int>
     */
    private array $keys = [];

    /**
     * @var list<IPair<TKey, TValue>>
     */
    private array $values = [];

    private int $pointer = 0;

    /**
     * @param IPair<TKey, TValue> ...$pairs
     */
    public function __construct(IPair ...$pairs)
    {
        foreach ($pairs as $pair) {
            $this->offsetSet($pair->key(), $pair->value());
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($this->toInternalKey($offset), $this->keys);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('The specified key does not exist.');
        }

        return $this->values[$this->keys[$this->toInternalKey($offset)]]->value();
    }

    /**
     * @inheritdoc
     * @param array-key|object $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $internalKey = $this->toInternalKey($offset);
        /** @var Pair<TKey, TValue> $pair */
        $pair = new Pair($offset, $value);

        if ($this->offsetExists($internalKey)) {
            $this->values[$this->keys[$internalKey]] = $pair;
            return;
        }

        $this->keys[$internalKey] = count($this->values);
        $this->values[] = $pair;
    }

    public function offsetUnset(mixed $offset): void
    {
        $internalKey = $this->toInternalKey($offset);

        if (!$this->offsetExists($internalKey)) {
            throw new \OutOfBoundsException('The specified key does not exist.');
        }

        $index = $this->keys[$internalKey];

        if ($index === count($this->values) - 1) {
            unset($this->keys[$internalKey], $this->values[$index]);
            return;
        }

        unset($this->values[$this->keys[$internalKey]]);

        $this->values = array_values($this->values);
        foreach ($this->values as $index => $pair) {
            $this->keys[$this->toInternalKey($pair->key())] = $index;
        }
    }

    public function current(): mixed
    {
        return $this->values[$this->pointer]->value();
    }

    public function next(): void
    {
        $this->pointer++;
    }

    public function key(): mixed
    {
        return $this->values[$this->pointer]->key();
    }

    public function valid(): bool
    {
        return isset($this->values[$this->pointer]);
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function count(): int
    {
        return count($this->values);
    }

    private function toInternalKey(mixed $publicKey): string
    {
        return match (gettype($publicKey)) {
            'string' => $publicKey,
            'integer' => (string) $publicKey,
            'object' => $publicKey instanceof Hashable ? $publicKey->getHash() : spl_object_hash($publicKey),
            default => throw new \InvalidArgumentException('Unsupported offset type'),
        };
    }
}
