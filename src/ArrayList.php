<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;
use Random\Engine\Secure;
use Random\Randomizer;

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

    public function keys(): ISet
    {
        return new Set(array_keys($this->values));
    }

    public function values(): IList
    {
        return clone $this;
    }

    public function toList(): IList
    {
        return $this->values();
    }

    public function toSet(): ISet
    {
        return new Set($this->values);
    }

    public function toDictionary(): IDictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($key, $value),
            array_keys($this->values),
            $this->values
        ));
    }


    public function toArray(): array
    {
        return $this->values;
    }

    public function apply(callable $selector): void
    {
        for ($i = 0; $i < $this->count(); $i++) {
            $this->values[$i] = $selector($this->values[$i], $i);
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

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (array $chunk) => new ArrayList($chunk), array_chunk($this->values, $length)));
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

    public function diff(ICollection ...$collections): IDictionary
    {
        $values = array_diff(
            $this->values,
            ...array_map(fn (ICollection $collection) => $collection->values()->toArray(), $collections)
        );

        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($key, $value),
            array_keys($values),
            $values
        ));
    }

    /**
     * @inheritdoc
     * @return self<T>
     */
    public function filter(callable $selector): self
    {
        return new self(array_filter($this->values, $selector, ARRAY_FILTER_USE_BOTH));
    }

    public function flip(): IDictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($value, $key),
            array_keys($this->values),
            $this->values
        ));
    }

    public function intersect(ICollection ...$collections): IDictionary
    {
        $values = array_intersect(
            $this->values,
            ...array_map(fn (ICollection $collection) => $collection->values()->toArray(), $collections)
        );

        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($key, $value),
            array_keys($values),
            $values
        ));
    }

    /**
     * @inheritdoc
     *
     * @template TNewValue
     *
     * @param callable(T, int=): TNewValue $selector
     *
     * @return self<TNewValue>
     */
    public function map(callable $selector): self
    {
        return new self(array_map($selector, $this->values, array_keys($this->values)));
    }

    public function merge(ICollection ...$collections): IDictionary
    {
        $merged = $this->toDictionary();

        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function padLeft(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new \OutOfRangeException('The length must be greater than 0.');
        }

        $this->pad($length * -1, $value);

        return $this;
    }

    public function padRight(int $length, mixed $value): self
    {
        if ($length < 1) {
            throw new \OutOfRangeException('The length must be greater than 0.');
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

    public function random(int $count = 1): IDictionary
    {
        $this->assertIsNotEmpty();

        if ($count > $this->count()) {
            throw new \OutOfRangeException('The count must be less than or equal to the number of elements in the list.');
        }

        $keys = array_rand($this->values, $count);

        return new Dictionary(...array_map(
            fn ($key) => new Pair($key, $this->values[$key]),
            is_array($keys) ? $keys : [$keys]
        ));
    }

    public function secureRandom(int $count = 1): IDictionary
    {
        $this->assertIsNotEmpty();

        if ($count > $this->count()) {
            throw new \OutOfRangeException('The count must be less than or equal to the number of elements in the list.');
        }

        $randomizer = new Randomizer(new Secure());

        $keys = $randomizer->pickArrayKeys($this->values, $count);

        return new Dictionary(...array_map(fn ($key) => new Pair($key, $this->values[$key]), $keys));
    }

    public function reduce(callable $selector, mixed $initial = null): mixed
    {
        return array_reduce($this->values, $selector, $initial);
    }

    public function search(mixed $value): int|null
    {
        return array_search($value, $this->values, true) ?: null;
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

    public function sort(SortDirection $direction = SortDirection::ASCENDING): ArrayList
    {
        /** @var callable(T, T): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->getMultiplier() * ($a <=> $b);
        return $this->sortBy($selector);
    }

    public function sortBy(callable $selector): ArrayList
    {
        usort($this->values, $selector);
        $this->values = array_values($this->values);

        return $this;
    }


    private function assertIsNotEmpty(): void
    {
        if ($this->count() === 0) {
            throw new \UnderflowException('The list is empty.');
        }
    }
}
