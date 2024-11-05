<?php

declare(strict_types=1);

namespace Axonode\Collections;

use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;
use Random\Engine\Secure;
use Random\Randomizer;

/**
 * Represents a collection of key-value pairs.
 *
 * @template TKey
 * @template TValue
 *
 * @implements IDictionary<TKey, TValue>
 */
final class Dictionary implements IDictionary, Hashable
{
    use GeneratesObjectHash;

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

    public static function combine(ISet $keys, IList $values): IDictionary
    {
        if ($keys->count() !== $values->count()) {
            throw new \ValueError('The number of keys and values do not match.');
        }

        return new self(
            ...array_map(
                static fn (mixed $key, mixed $value) => new Pair($key, $value),
                $keys->toArray(),
                $values->toArray()
            )
        );
    }

    public function keys(): ISet
    {
        return new Set(array_map(fn (IPair $pair) => $pair->key(), $this->values));
    }

    public function values(): IList
    {
        return new ArrayList(array_map(fn (IPair $pair) => $pair->value(), $this->values));
    }

    public function toList(): IList
    {
        return $this->values();
    }

    public function toSet(): ISet
    {
        return $this->toList()->toSet();
    }

    public function toDictionary(): IDictionary
    {
        return clone $this;
    }

    public function apply(callable $selector): void
    {
        foreach ($this->values as $offset => $pair) {
            $this->values[$offset] = $pair->withValue($selector($pair->value(), $pair->key()));
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
     * @param TKey $offset
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        /** @var Pair<TKey, TValue> $pair */
        $pair = new Pair($offset, $value);

        $internalKey = $this->toInternalKey($offset);

        if ($this->offsetExists($offset)) {
            $this->values[$this->keys[$internalKey]] = $pair;
            return;
        }

        $this->keys[$internalKey] = count($this->values);
        $this->values[] = $pair;
    }

    public function offsetUnset(mixed $offset): void
    {
        if (!$this->offsetExists($offset)) {
            throw new \OutOfBoundsException('The specified key does not exist.');
        }

        $internalKey = $this->toInternalKey($offset);

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

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (mixed $valueChunk) => new Dictionary(...$valueChunk), array_chunk($this->values, $length)));
    }

    public function contains(mixed $value): bool
    {
        return $this->search($value) !== null;
    }

    public function countValues(): IDictionary
    {
        $result = new Dictionary();

        foreach ($this->values as $value) {
            $result[$value->value()] = $result->offsetExists($value->value()) ? $result[$value->value()] + 1 : 1;
        }

        return $result;
    }

    public function diff(ICollection ...$collections): IDictionary
    {
        return new Dictionary(
            ...array_filter(
                $this->values,
                static fn (IPair $value) => !empty(array_diff(
                    [$value->value()],
                    ...array_map(static fn (ICollection $collection) => $collection->values()->toArray(), $collections)
                )),
            )
        );
    }

    /**
     * @inheritdoc
     * @return self<TKey, TValue>
     */
    public function filter(callable $selector): self
    {
        return new self(...array_filter(
            $this->values,
            static fn (IPair $pair) => $selector($pair->value(), $pair->key())
        ));
    }

    public function flip(): IDictionary
    {
        return new self(
            ...array_map(
                static fn (IPair $pair) => new Pair($pair->value(), $pair->key()),
                $this->values
            )
        );
    }

    public function intersect(ICollection ...$collections): IDictionary
    {
        return new Dictionary(
            ...array_filter(
                $this->values,
                static fn (IPair $value) => !empty(array_intersect(
                    [$value->value()],
                    ...array_map(static fn (ICollection $collection) => $collection->values()->toArray(), $collections)
                )),
            )
        );
    }

    /**
     * @inheritdoc
     *
     * @template TNewValue
     *
     * @param callable(TValue, TKey=): TNewValue $selector
     *
     * @return self<TKey, TNewValue>
     */
    public function map(callable $selector): self
    {
        return new self(...array_map(
            static fn ($value, $key) => new Pair($key, $selector($value, $key)),
            $this->values()->toArray(),
            $this->keys()->toArray()
        ));
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

        /** @var Pair<TKey, TValue> $item */
        $item = array_pop($this->values);

        unset($this->keys[$this->toInternalKey($item->key())]);

        return $item->value();
    }

    public function random(int $count = 1): IDictionary
    {
        $this->assertIsNotEmpty();

        if ($count > $this->count()) {
            throw new \OutOfRangeException('The count must be less than or equal to the number of elements in the dictionary.');
        }

        $keys = array_rand($this->values, $count);

        return new Dictionary(...array_map(
            fn (int $key) => $this->values[$key],
            is_array($keys) ? $keys : [$keys]
        ));
    }

    public function secureRandom(int $count = 1): IDictionary
    {
        $this->assertIsNotEmpty();

        if ($count > $this->count()) {
            throw new \OutOfRangeException('The count must be less than or equal to the number of elements in the dictionary.');
        }

        $randomizer = new Randomizer(new Secure());

        $keys = $randomizer->pickArrayKeys($this->values, $count);

        return new Dictionary(...array_map(fn (int $key) => $this->values[$key], $keys));
    }

    public function reduce(callable $selector, mixed $initial = null): mixed
    {
        return $this->values()->reduce($selector, $initial);
    }

    public function search(mixed $value): mixed
    {
        foreach ($this->values as $pair) {
            if ($pair->value() === $value) {
                return $pair->key();
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     * @return ArrayList<TKey>
     */
    public function searchAll(callable $selector): ArrayList
    {
        $keys = [];

        foreach ($this->values as $pair) {
            if ($selector($pair->value())) {
                $keys[] = $pair->key();
            }
        }

        return new ArrayList($keys);
    }

    public function shift(): mixed
    {
        $this->assertIsNotEmpty();

        /** @var Pair<TKey, TValue> $item */
        $item = array_shift($this->values);

        $this->keys = [];
        foreach ($this->values as $index => $pair) {
            $this->keys[$this->toInternalKey($pair->key())] = $index;
        }

        return $item->value();
    }

    public function sort(SortDirection $direction = SortDirection::ASCENDING): Dictionary
    {
        /** @var callable(IPair<TKey, TValue>, IPair<TKey, TValue>): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->getMultiplier() * ($a->value() <=> $b->value());
        return $this->sortBy($selector);
    }

    public function sortBy(callable $selector): Dictionary
    {
        usort($this->values, $selector);

        foreach ($this->values as $index => $pair) {
            $this->keys[$this->toInternalKey($pair->key())] = $index;
        }

        return $this;
    }

    /**
     * @param TKey $publicKey
     */
    private function toInternalKey(mixed $publicKey): string
    {
        return match (gettype($publicKey)) {
            'string' => $publicKey,
            'integer', 'resource', 'resource (closed)', 'double' => (string) $publicKey,
            'object' => $publicKey instanceof Hashable ? $publicKey->getHash() : spl_object_hash($publicKey),
            'NULL' => 'type::null',
            'array' => serialize($publicKey),
            'boolean' => $publicKey ? 'true' : 'false',
            default => throw new \TypeError('Invalid key type'),
        };
    }

    private function assertIsNotEmpty(): void
    {
        if ($this->count() === 0) {
            throw new \UnderflowException('The dictionary is empty.');
        }
    }
}
