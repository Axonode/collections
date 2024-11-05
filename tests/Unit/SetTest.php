<?php

declare(strict_types=1);

use Axonode\Collections\ArrayList;
use Axonode\Collections\Dictionary;
use Axonode\Collections\Object\Hashable;
use Axonode\Collections\Pair;
use Axonode\Collections\Set;
use Axonode\Collections\SortDirection;

it('can handle supported types', function (Set $set, int $key, mixed $expectedValue, ?callable $cleanUp = null) {
    try {
        expect($set[$key])->toBe($expectedValue);
    } finally {
        if ($cleanUp !== null) {
            $cleanUp();
        }
    }
})->with([
    [
        new Set([1, 2]),
        0,
        1,
    ],
    [
        new Set(['a', 'b']),
        1,
        'b',
    ],
    [
        new Set([1.1, 1.2]),
        0,
        1.1,
    ],
    function () {
        $value = fopen('php://temp', 'r');

        return [
            new Set([$value]),
            0,
            $value,
            static fn () => fclose($value),
        ];
    },
    function () {
        $value = fopen('php://temp', 'r');
        fclose($value);

        return [
            new Set([$value]),
            0,
            $value,
        ];
    },
    [
        new set([SortDirection::ASCENDING, SortDirection::DESCENDING]),
        0,
        SortDirection::ASCENDING,
    ],
    function () {
        $value = new class () implements Hashable {
            public function getHash(): string
            {
                return spl_object_hash($this);
            }
        };

        return [
            new Set([$value]),
            0,
            $value,
        ];
    },
    [
        new Set([null]),
        0,
        null,
    ],
    [
        new Set([[1, 2, 3], [4, 5, 6]]),
        0,
        [1, 2, 3],
    ],
    [
        new Set([true, false]),
        0,
        true,
    ]
]);

it('creates a set of the given values', function (array $initialValues, Set $expectedSet) {
    expect(new Set($initialValues))->toEqual($expectedSet);
})->with([
    [[], new Set()],
    [[1, 2, 3], new Set([1, 2, 3])],
    [['a', 'b', 'c', 'd', 'a', 'x', 'c', 'z'], new Set(['a', 'b', 'c', 'd', 'x', 'z'])],
]);

it('loops through the values in the set', function () {
    $set = new Set(['a', 'b', 'c', 'd', 'x', 'z']);
    $expectedValues = ['a', 'b', 'c', 'd', 'x', 'z'];

    foreach ($set as $offset => $value) {
        expect($value)->toBe($expectedValues[$offset]);
    }
});

it('determines if an offset exists in the set', function (Set $set, int $offset, bool $expectedResult) {
    expect($set->offsetExists($offset))->toBe($expectedResult);
})->with([
    [new Set(), 0, false],
    [new Set([1, 2, 3]), 0, true],
    [new Set([1, 2, 3]), 1, true],
    [new Set([1, 2, 3]), 5, false],
]);

it('throws exception when trying retrieve non-existing offset', function (Set $set, int $offset) {
    $set->offsetGet($offset);
})->with([
    [new Set(), 0],
    [new Set([1, 2, 3]), 5],
])->throws(OutOfBoundsException::class);

it('returns the value at the given offset', function () {
    $list = new Set([1, 2, 3]);

    expect($list->offsetGet(1))->toBe(2);
});

it('throws exception when trying to set offset to a value already exists', function () {
    $set = new Set([1, 2, 3, 4]);
    $set->offsetSet(1, 4);
})->throws(InvalidArgumentException::class);

it('throws exception when trying to set an out of range offset', function (Set $set, int $offset) {
    $set->offsetSet($offset, 1);
})->with([
    [new Set(), 1],
    [new Set([1, 2, 3]), 5],
    [new Set([1, 2, 3]), -3],
])->throws(OutOfRangeException::class);

it('sets the offset to the given value', function (Set $set, int $offset, mixed $value) {
    $set->offsetSet($offset, $value);
    expect($set[$offset])->toBe($value);
})->with([
    [new Set([1, 2, 3]), 1, 4],
    [new Set([1, 2, 3]), 2, 3],
]);

it('throws exception when trying to unset non-existing item', function (Set $set, int $offset) {
    $set->offsetUnset($offset);
})->with([
    [new Set(), 0],
    [new Set([1, 2, 3]), 5],
])->throws(OutOfBoundsException::class);

it('it unsets the value at the given offset', function () {
    $set = new Set([1, 2, 3]);
    $set->offsetUnset(1);

    expect($set)->toEqual(new Set([1, 3]));
});

it('returns the number of values in the set', function (Set $set, int $expectedCount) {
    expect($set->count())->toBe($expectedCount);
})->with([
    [new Set(), 0],
    [new Set([1, 2, 3]), 3],
]);

it('returns if the given value is present in the set', function (Set $set, mixed $value, bool $expectedResult) {
    expect($set->contains($value))->toBe($expectedResult);
})->with([
    [new Set(), 1, false],
    [new Set([1, 2, 3]), 1, true],
    [new Set([1, 2, 3]), 4, false],
]);

it('adds the given value to the set', function (Set $set, mixed $value, Set $expectedSet) {
    $set->add($value);
    expect($set)->toEqual($expectedSet);
})->with([
    [new Set(), 1, new Set([1])],
    [new Set([1, 2, 3]), 4, new Set([1, 2, 3, 4])],
    [new Set([true]), false, new Set([true, false])],
    [new Set(['a', 'b', 'c']), 'b', new Set(['a', 'b', 'c'])],
]);

it('throws exception when trying to remove value from set which is not present', function () {
    $set = new Set([1, 2, 3]);
    $set->remove(4);
})->throws(OutOfBoundsException::class);

it('removes the given value from the set', function (Set $set, mixed $value, Set $expectedSet) {
    $set->remove($value);
    expect($set)->toEqual($expectedSet);
})->with([
    [new Set([1, 2, 3]), 1, new Set([2, 3])],
    [new Set([1, 2, 3]), 3, new Set([1, 2])],
    [new Set(['a', 'b', 'c']), 'b', new Set(['a', 'c'])],
]);

it('chunks itself into lists with given length', function () {
    $set = new Set([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    $chunks = $set->chunk(3);

    expect($chunks)->toEqual(new ArrayList([
        new Set([1, 2, 3]),
        new Set([4, 5, 6]),
        new Set([7, 8, 9]),
        new Set([10]),
    ]));
});

it('returns a list of the keys from the set', function () {
    $set = new Set([1, 2, 3]);
    $keys = $set->keys();

    expect($keys)->toEqual(new Set([0, 1, 2]));
});

it('returns a list of the values from the set', function () {
    $set = new Set([1, 2, 3]);
    $values = $set->values();

    expect($values)->toEqual(new ArrayList([1, 2, 3]));
});

it('creates a dictionary of the items not present in the other collections', function () {
    $set = new Set([1, 2, 34, 4, 48, 5, 10]);
    $list = new ArrayList([4, 5, 6, 7, 8]);
    $dictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1));
    $anotherSet = new Set([34, 10]);

    $expectedDiff = new Dictionary(new Pair(1, 2), new Pair(4, 48));

    expect($set->diff($list, $dictionary, $anotherSet))->toEqual($expectedDiff);
});

it('creates a dictionary with flipped keys and values', function () {
    $set = new Set(['a', 'b', 'c']);
    $expectedDictionary = new Dictionary(new Pair('a', 0), new Pair('b', 1), new Pair('c', 2));

    expect($set->flip())->toEqual($expectedDictionary);
});

it('creates a dictionary of the items are present in all the other collections', function () {
    $set = new Set([1, 2, 34, 7, 4, 48, 5, 10]);
    $otherList = new ArrayList([4, 5, 6, 7, 8]);
    $dictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1), new Pair(3, 7), new Pair(4, 5));
    $otherSet = new Set([34, 5, 10, 7]);

    $expected = new Dictionary(new Pair(3, 7), new Pair(6, 5));

    expect($set->intersect($otherList, $dictionary, $otherSet))->toEqual($expected);
});

it('creates a new set by applying the provided callback to each items in the set', function () {
    $set = new Set(['a', 'b', 'c', 'd', 'e']);
    $callback = static fn (string $value, int $key) => $key . strtoupper($value);

    $expectedSet = new Set(['0A', '1B', '2C', '3D', '4E']);

    expect($set->map($callback))->toEqual($expectedSet);
});

it('merges the items from all collections into a new dictionary', function () {
    $set = new Set(['g', 'h', 'c']);
    $list = new ArrayList(['a', 'b', 'c']);
    $dictionary = new Dictionary(new Pair(0, 'b'), new Pair(23, 'z'), new Pair(4, 'x'));

    $expectedDictionary = new Dictionary(
        new Pair(0, 'b'),
        new Pair(1, 'b'),
        new Pair(2, 'c'),
        new Pair(23, 'z'),
        new Pair(4, 'x'),
    );

    expect($set->merge($list, $dictionary))->toEqual($expectedDictionary);
});

it('returns a list containing the items of the set', function () {
    $set = new Set(['a', 'b', 'c']);
    $list = $set->toList();

    expect($list)->toEqual(new ArrayList(['a', 'b', 'c']));
});

it('returns a new copy from itself', function () {
    $set = new Set(['a', 'b', 'c']);
    $copy = $set->toSet();

    expect($copy)->toEqual($set)->and($copy)->not->toBe($set);
});

it('applies the given selector to all of its elements', function () {
    $set = new Set(['a', 'b', 'c']);
    $selector = static fn (string $value) => strtoupper($value);
    $set->apply($selector);

    $expectedSet = new Set(['A', 'B', 'C']);

    expect($set)->toEqual($expectedSet);
});

it('returns a new set containing all the elements from itself which passes the given selector', function () {
    $set = new Set(['a', 'b', 'c', 'd', 'e']);
    $selector = static fn (string $value) => in_array($value, ['a', 'c', 'e']);
    $newSet = $set->filter($selector);

    $expectedSet = new Set(['a', 'c', 'e']);

    expect($newSet)->toEqual($expectedSet);
});

it('throws exception when trying to invoke method on set which could not be invoked on empty sets', function (string $method, array $args) {
    $set = new Set();
    $set->{$method}(...$args);
})->with([
    ['pop', []],
    ['random', [2]],
    ['secureRandom', [2]],
    ['shift', []],
])->throws(UnderflowException::class, 'The set is empty.');

it('removes and returns the last element of the set', function () {
    $set = new Set(['a', 'b', 'c']);
    $last = $set->pop();

    expect($last)->toBe('c')->and($set)->toEqual(new Set(['a', 'b']));
});

it('pushes the given values to the end of the set', function () {
    $set = new Set(['a', 'b', 'c']);
    $set->push('d', 'b', 'e');

    expect($set)->toEqual(new Set(['a', 'b', 'c', 'd', 'e']));
});

it('throws exception when trying to retrieve more random elements from the set than it contains', function (string $method) {
    $set = new Set(['a', 'b', 'c']);
    $set->{$method}(4);
})->with([
    ['random'],
    ['secureRandom'],
])->throws(OutOfRangeException::class, 'The count must be less than or equal to the number of elements in the list.');

it('returns a dictionary with count elements randomly picked from the set', function (string $method) {
    $set = new Set(['a', 'b', 'c', 'd', 'e']);

    $randomElements = $set->{$method}(3);

    expect($randomElements)->toBeInstanceOf(Dictionary::class)
        ->and($randomElements->count())->toBe(3);

    foreach ($randomElements as $value) {
        expect($set->contains($value))->toBeTrue();
    }
})->with([
    ['random'],
    ['secureRandom'],
]);

it('reduces the set to a single value by passing all elements to the given selector', function () {
    $set = new Set([1, 2, 3, 4, 5]);
    $selector = static fn (int $carry, int $value) => $carry + $value;

    expect($set->reduce($selector, 0))->toBe(15);
});

it('search for the first occurrence of the given element in the set', function (string $value, ?int $expectedOffset) {
    $set = new Set(['a', 'b', 'c', 'd', 'e']);

    $offset = $set->search($value);

    expect($offset)->toBe($expectedOffset);
})->with([
    ['a', 0],
    ['b', 1],
    ['c', 2],
    ['d', 3],
    ['e', 4],
    ['f', null],
]);

it('removes and returns the first element of the set', function () {
    $set = new Set(['a', 'b', 'c']);
    $first = $set->shift();

    expect($first)->toBe('a')->and($set)->toEqual(new Set(['b', 'c']));
});

it('sorts the set in the given direction', function (SortDirection $direction, Set $expectedSet) {
    $set = new Set([5, 2, 7, 4, 1, 3, 6]);
    $set->sort($direction);

    expect($set)->toEqual($expectedSet);
})->with([
    [SortDirection::ASCENDING, new Set([1, 2, 3, 4, 5, 6, 7])],
    [SortDirection::DESCENDING, new Set([7, 6, 5, 4, 3, 2, 1])],
]);

it('sorts the set by the given selector', function () {
    $set = new Set(['bb', 'eeeee', 'ccc', 'a', 'dddd']);
    $set->sortBy(static fn (string $a, string $b) => strlen($a) <=> strlen($b));

    expect($set)->toEqual(new Set(['a', 'bb', 'ccc', 'dddd', 'eeeee']));
});
