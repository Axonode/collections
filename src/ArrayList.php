<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Contracts\IDictionary;
use Axonode\Collections\Contracts\IList;
use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;
use Axonode\Collections\Traits\CollectionShared;
use Axonode\Collections\Traits\CountsElements;
use Axonode\Collections\Traits\Enumerator;
use Axonode\Collections\Traits\ListShared;
use OutOfBoundsException;
use OutOfRangeException;

/**
 * Represents a list of items.
 *
 * @template T
 *
 * @implements IList<T>
 */
final class ArrayList implements IList, Hashable
{
    use GeneratesObjectHash;
    use CollectionShared;
    /** @use ListShared<T> */
    use ListShared;
    use Enumerator;
    use CountsElements;

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

    public function apply(callable $selector): void
    {
        for ($i = 0; $i < $this->count(); $i++) {
            $this->values[$i] = $selector($this->values[$i], $i);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    public function &offsetGet(mixed $offset): mixed
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        return $this->values[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset < 0 || $offset > $this->count()) {
            throw new OutOfRangeException("The offset ($offset) must be between 0 and the length ({$this->count()}) of the list");
        }

        // @phpstan-ignore assign.propertyType
        $this->values[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfBoundsException("The specified key ($offset) does not exist.");
        }

        unset($this->values[$offset]);
        $this->values = array_values($this->values);
    }

    public function contains(mixed $value): bool
    {
        return in_array($value, $this->values, true);
    }

    public function countValues(): IDictionary
    {
        $result = new Dictionary();

        foreach ($this->values as $value) {
            $result[$value] = $result->offsetExists($value) ? $result[$value] + 1 : 1;
        }

        return $result;
    }

    public function padLeft(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new OutOfRangeException('The length must be greater than 0.');
        }

        $this->pad($length * -1, $value);

        return $this;
    }

    public function padRight(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new OutOfRangeException('The length must be greater than 0.');
        }

        $this->pad($length, $value);

        return $this;
    }

    private function pad(int $length, mixed $value): void
    {
        $this->values = array_pad($this->values, $length, $value);
    }

    public function pop(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $value */
        $value = array_pop($this->values);

        return $value;
    }

    public function push(mixed ...$values): void
    {
        array_push($this->values, ...$values);
    }

    /**
     * @inheritdoc
     * @return ArrayList<int>
     */
    public function searchAll(callable $selector): ArrayList
    {
        $keys = [];

        foreach ($this->values as $key => $value) {
            if ($selector($value)) {
                $keys[] = $key;
            }
        }

        return new ArrayList($keys);
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var T $value */
        $value = array_shift($this->values);

        return $value;
    }

    public function sortBy(callable $selector): ArrayList
    {
        usort($this->values, $selector);
        $this->values = array_values($this->values);

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
