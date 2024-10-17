<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a list of items.
 *
 * @template T
 *
 * @implements IArrayList<T>
 */
final class ArrayList implements IArrayList
{
    /**
     * @var list<T>
     */
    private array $values;

    private int $pointer = 0;

    /**
     * @param T[] $items
     */
    public function __construct(array $items = [])
    {
        $this->values = array_values($items);
    }

    public function current(): mixed
    {
        return $this->values[$this->pointer];
    }

    public function next(): void
    {
        $this->pointer++;
    }

    public function key(): mixed
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

    public function &offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        return $this->values[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset < 0 || $offset > $this->count()) {
            throw new \OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        // @phpstan-ignore assign.propertyType
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        unset($this->values[$offset]);
        $this->values = array_values($this->values);
    }

    public function count(): int
    {
        return count($this->values);
    }
}
