<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;
use Random\Engine\Secure;
use Random\Randomizer;

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

    public function keys(): ISet
    {
        return new self(array_keys($this->values));
    }

    public function values(): IList
    {
        return new ArrayList($this->values);
    }

    public function toList(): IList
    {
        return $this->values();
    }

    public function toSet(): ISet
    {
        return clone $this;
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
        $pointer = $this->pointer;

        foreach ($this as $key => $value) {
            $this[$key] = $selector($value, $key);
        }

        $this->pointer = $pointer;
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

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (array $chunk) => new Set($chunk), array_chunk($this->values, $length)));
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
        $offset = array_search($value, $this->values, true);
        return $offset !== false ? $offset : null;
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

    public function sort(SortDirection $direction = SortDirection::ASCENDING): Set
    {
        /** @var callable(T, T): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->getMultiplier() * ($a <=> $b);
        return $this->sortBy($selector);
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

    /**
     * @param T $value
     */
    private function toInternalKey(mixed $value): string
    {
        return match (gettype($value)) {
            'string' => $value,
            'integer', 'resource', 'resource (closed)', 'double' => (string) $value,
            'object' => $value instanceof Hashable ? $value->getHash() : spl_object_hash($value),
            'NULL' => 'null',
            'array' => serialize($value),
            'boolean' => $value ? 'true' : 'false',
            default => throw new \InvalidArgumentException('The specified key type is not supported.'),
        };
    }

    private function assertIsNotEmpty(): void
    {
        if ($this->count() === 0) {
            throw new \UnderflowException('The set is empty.');
        }
    }
}
