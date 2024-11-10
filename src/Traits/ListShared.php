<?php

declare(strict_types=1);

namespace Axonode\Collections\Traits;

use Axonode\Collections\ArrayList;
use Axonode\Collections\Contracts\ICollection;
use Axonode\Collections\Contracts\IDictionary;
use Axonode\Collections\Contracts\IList;
use Axonode\Collections\Contracts\ISet;
use Axonode\Collections\Dictionary;
use Axonode\Collections\Pair;
use Axonode\Collections\Set;
use Axonode\Collections\SortDirection;
use OutOfRangeException;
use Random\Engine\Secure;
use Random\Randomizer;

/**
 * @internal
 *
 * @template T
 */
trait ListShared
{
    public function keys(): ISet
    {
        return new Set(array_keys($this->values));
    }

    public function values(): IList
    {
        return new ArrayList($this->values);
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

    public function chunk(int $length): IList
    {
        return new ArrayList(array_map(fn (array $chunk) => new self($chunk), array_chunk($this->values, $length)));
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

    public function flip(): IDictionary
    {
        return new Dictionary(...array_map(
            static fn ($key, $value) => new Pair($value, $key),
            array_keys($this->values),
            $this->values
        ));
    }

    public function random(int $count = 1): IDictionary
    {
        $this->assertIsNotEmpty();

        if ($count > $this->count()) {
            throw new OutOfRangeException('The count must be less than or equal to the number of elements in the list.');
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
            throw new OutOfRangeException('The count must be less than or equal to the number of elements in the list.');
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

    public function sort(SortDirection $direction = SortDirection::ASCENDING): self
    {
        /** @var callable(T, T): int<-1, 1> $selector */
        $selector = static fn ($a, $b) => $direction->getMultiplier() * ($a <=> $b);
        return $this->sortBy($selector);
    }
}
