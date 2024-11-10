<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Contracts\ISet;
use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;
use Axonode\Collections\Traits\CollectionShared;
use Axonode\Collections\Traits\CountsElements;
use Axonode\Collections\Traits\HashKeys;
use Axonode\Collections\Traits\Enumerator;
use Axonode\Collections\Traits\ListShared;
use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;

/**
 * Represents a list of unique items.
 *
 * @template T
 *
 * @implements ISet<T>
 */
final class Set implements ISet, Hashable
{
    use GeneratesObjectHash;
    use CollectionShared;
    use HashKeys;
    /** @use ListShared<T> */
    use ListShared;
    use Enumerator;
    use CountsElements;

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

    public function apply(callable $selector): void
    {
        $pointer = $this->pointer;

        foreach ($this as $key => $value) {
            $this[$key] = $selector($value, $key);
        }

        $this->pointer = $pointer;
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException('The specified key does not exist.');
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
            throw new OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        if ($this->contains($value)) {
            if ($this->offsetGet($offset) === $value) {
                return;
            }

            throw new InvalidArgumentException('The specified value is already present in the set.');
        }

        $internalKey = $this->toInternalKey($this->values[$offset]);
        $this->values[$offset] = $value;
        unset($this->keys[$internalKey]);
        $this->keys[$this->toInternalKey($value)] = $offset;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException('The specified key does not exist.');
        }

        $this->remove($this->values[$offset]);
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
            throw new OutOfBoundsException('The specified value is not present in the set.');
        }

        unset($this->values[$this->keys[$this->toInternalKey($value)]]);
        $this->keys = [];
        $this->values = array_values($this->values);
        foreach ($this->values as $index => $item) {
            $this->keys[$this->toInternalKey($item)] = $index;
        }
    }

    public function pop(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $item */
        $item = array_pop($this->values);

        unset($this->keys[$this->toInternalKey($item)]);

        return $item;
    }

    public function push(mixed ...$values): void
    {
        foreach ($values as $value) {
            $this->add($value);
        }
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $item */
        $item = array_shift($this->values);

        $this->keys = [];
        foreach ($this->values as $offset => $value) {
            $this->keys[$this->toInternalKey($value)] = $offset;
        }

        return $item;
    }

    public function sortBy(callable $selector): Set
    {
        usort($this->values, $selector);

        $this->keys = [];
        foreach ($this->values as $index => $value) {
            $this->keys[$this->toInternalKey($value)] = $index;
        }

        return $this;
    }

    private function valueAt(int $at): mixed
    {
        return $this->values[$at];
    }

    private function keyAt(int $at): int
    {
        return $at;
    }
}
