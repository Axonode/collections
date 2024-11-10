<?php

declare(strict_types=1);

use Axonode\Collections\ArrayList;
use Axonode\Collections\Dictionary;
use Axonode\Collections\Object\Hashable;
use Axonode\Collections\Pair;
use Axonode\Collections\Set;
use Axonode\Collections\SortDirection;

it('creates empty dictionary when no items provided', function () {
    $dictionary = new Dictionary();
    expect($dictionary->count())->toBe(0);
});

it('can handle supported types as key', function (Dictionary $dictionary, mixed $key, mixed $expectedValue, ?callable $cleanUp = null) {
    try {
        expect($dictionary[$key])->toBe($expectedValue);
    } finally {
        if ($cleanUp !== null) {
            $cleanUp();
        }
    }
})->with([
    [
        new Dictionary(new Pair('a', 1), new Pair('b', 2)),
        'a',
        1,
    ],
    [
        new Dictionary(new Pair(1, 'a'), new Pair(2, 'b')),
        1,
        'a',
    ],
    [
        new Dictionary(new Pair(1.1, 'a'), new Pair(1.2, 'b')),
        1.1,
        'a',
    ],
    function () {
        $key = fopen('php://temp', 'r');
        $value = new stdClass();

        return [
            new Dictionary(new Pair($key, $value)),
            $key,
            $value,
            static fn () => fclose($key),
        ];
    },
    function () {
        $key = fopen('php://temp', 'r');
        fclose($key);
        $value = new stdClass();

        return [
            new Dictionary(new Pair($key, $value)),
            $key,
            $value,
        ];
    },
    [
        new Dictionary(new Pair(SortDirection::ASCENDING, 'asc'), new Pair(SortDirection::DESCENDING, 'desc')),
        SortDirection::ASCENDING,
        'asc',
    ],
    function () {
        $key = new class () implements Hashable {
            public function getHash(): string
            {
                return spl_object_hash($this);
            }
        };
        $value = 15;

        return [
            new Dictionary(new Pair($key, $value), new Pair(new stdClass(), 34)),
            $key,
            $value,
        ];
    },
    [
        new Dictionary(new Pair(null, 123)),
        null,
        123,
    ],
    [
        new Dictionary(new Pair([1, 2, 3], true), new Pair([4, 5, 6], false)),
        [1, 2, 3],
        true,
    ],
    [
        new Dictionary(new Pair(true, ['abc']), new Pair(false, ['def'])),
        true,
        ['abc'],
    ]
]);

it('can return whether the offset exists', function (array $initialItems, mixed $offset, bool $exists) {
    $dictionary = new Dictionary(...$initialItems);
    expect($dictionary->offsetExists($offset))->toBe($exists);
})->with([
    [[], 'a', false],
    [[new Pair(1, 'a')], 2, false],
    [[new Pair(1, 'a')], 1, true],
    [[new Pair('a', 1)], 'b', false],
    [[new Pair('a', 1)], 'a', true],
    [[new Pair(new stdClass(), 1)], new stdClass(), false],
    function () {
        $key = new class () implements Hashable {
            public function getHash(): string
            {
                return spl_object_hash($this);
            }
        };
        return [[new Pair($key, 'a')], $key, true];
    }
]);

it('throws exception when trying to retrieve non-existing offset', function (array $initialValues, mixed $offset) {
    $dictionary = new Dictionary(...$initialValues);
    $dictionary->offsetGet($offset);
})->with([
    [[], 'a'],
    [[new Pair(1, 'a')], 2],
])->throws(OutOfBoundsException::class, 'The specified key does not exist.');

it('returns the correct item', function (array $initialValues, mixed $offset, mixed $expectedValue) {
    $dictionary = new Dictionary(...$initialValues);
    expect($dictionary[$offset])->toBe($expectedValue);
})->with([
    [[new Pair('a', 2)], 'a', 2],
    function () {
        $key = new stdClass();
        return [[new Pair($key, 3)], $key, 3];
    }
]);

it('sets the given element at the given offset', function (Dictionary $dictionary, mixed $offset, mixed $value) {
    $dictionary[$offset] = $value;
    expect($dictionary[$offset])->toBe($value);
})->with([
    [new Dictionary(new Pair('a', 1)), 'b', 2],
    [new Dictionary(new Pair('a', 1)), 'a', 2],
    [new Dictionary(new Pair(new stdClass(), 3)), new stdClass(), 4],
    function () {
        $key = new stdClass();

        return [
            new Dictionary(new Pair($key, 4)),
            $key,
            5
        ];
    }
]);

it('throws exception when trying to unset non-existing offset', function () {
    $dictionary = new Dictionary(new Pair(new stdClass(), 15));
    unset($dictionary[new stdClass()]);
})->throws(OutOfBoundsException::class, 'The specified key does not exist.');

it('removes the item at the given offset', function (Dictionary $dictionary, mixed $offset, Dictionary $expectedDictionary) {
    unset($dictionary[$offset]);
    expect($dictionary)->toBe($dictionary);
})->with([
    [
        new Dictionary(new Pair('a', 1), new Pair('b', 2), new Pair('c', 3)),
        'c',
        new Dictionary(new Pair('a', 1), new Pair('b', 2))
    ],
    [
        new Dictionary(new Pair('a', 1), new Pair('b', 2), new Pair('c', 3)),
        'b',
        new Dictionary(new Pair('a', 1), new Pair('c', 3))
    ]
]);

it('returns the number of elements in the dictionary', function (Dictionary $dictionary, int $expectedCount) {
    expect($dictionary->count())->toBe($expectedCount);
})->with([
    [new Dictionary(), 0],
    [new Dictionary(new Pair('a', 1)), 1],
    [new Dictionary(new Pair('a', 1), new Pair('b', 3)), 2]
]);

it('can loop through the dictionary', function (Dictionary $dictionary, array $expectedKeys, array $expectedValues) {
    $i = 0;

    foreach ($dictionary as $key => $value) {
        expect($key)->toBe($expectedKeys[$i])
            ->and($value)->toBe($expectedValues[$i]);
        ++$i;
    }
})->with([
    function () {
        $key1 = new stdClass();
        $key1->a = 1;
        $key2 = new stdClass();
        $key2->a = 2;

        return [
            new Dictionary(new Pair($key1, 'a'), new Pair($key2, 'b')),
            [$key1, $key2],
            ['a', 'b']
        ];
    },
    [
        new Dictionary(new Pair('a', 1), new Pair('b', 2)),
        ['a', 'b'],
        [1, 2],
    ],
]);

it('chunks itself into lists with given length', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
        new Pair('d', 4),
        new Pair('e', 5),
    );

    $chunks = $dictionary->chunk(2);

    expect($chunks->count())->toBe(3)
        ->and($chunks[0])->toEqual(new Dictionary(new Pair('a', 1), new Pair('b', 2)))
        ->and($chunks[1])->toEqual(new Dictionary(new Pair('c', 3), new Pair('d', 4)))
        ->and($chunks[2])->toEqual(new Dictionary(new Pair('e', 5)));
});

it('returns a set of the keys from the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $keys = $dictionary->keys();

    expect($keys)->toEqual(new Set(['a', 'b', 'c']));
});

it('returns a list of the values from the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $expected = new ArrayList([1, 2, 3]);

    $values = $dictionary->values();
    $list = $dictionary->toList();

    expect($values)->toEqual($expected)->and($values)->toEqual($list);
});

it('returns a set of the values from the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 1),
        new Pair('c', 3),
    );

    $set = $dictionary->toSet();

    expect($set)->toEqual(new Set([1, 3]));
});

it('throws exception when trying to combine a set of keys and list of values with not matching number of elements', function () {
    $keys = new Set(['a', 'b', 'c']);
    $values = new ArrayList([1, 2]);

    Dictionary::combine($keys, $values);
})->throws(ValueError::class, 'The number of keys and values do not match.');

it('can combine a set of keys and list of values into a dictionary', function () {
    $keys = new Set(['a', 'b', 'c']);
    $values = new ArrayList([1, 2, 3]);

    $dictionary = Dictionary::combine($keys, $values);

    expect($dictionary)->toEqual(new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    ));
});

it('counts how many times a value is in the dictionary', function (Dictionary $dictionary, Dictionary $expectedCount) {
    expect($dictionary->countValues())->toEqual($expectedCount);
})->with([
    [new Dictionary(), new Dictionary()],
    [
        new Dictionary(
            new Pair('a', 1),
            new Pair('b', 2),
            new Pair('c', 3),
            new Pair('d', 1),
            new Pair('e', 2),
            new Pair('f', 1),
        ),
        new Dictionary(new Pair(1, 3), new Pair(2, 2), new Pair(3, 1))
    ],
]);

it('creates a dictionary of the items not present in the other collections', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 34),
        new Pair('d', 4),
        new Pair('e', 48),
        new Pair('f', 5),
        new Pair('g', 10),
    );
    $list = new ArrayList([4, 5, 6, 7, 8]);
    $otherDictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1));
    $set = new Set([34, 10]);

    $expectedDiff = new Dictionary(new Pair('b', 2), new Pair('e', 48));

    expect($dictionary->diff($list, $otherDictionary, $set))->toEqual($expectedDiff);
});

it('creates a dictionary with flipped keys and values', function () {
    $dictionary = new Dictionary(
        new Pair('a', 'z'),
        new Pair('b', 'y'),
        new Pair('c', 'x'),
    );

    $expectedFlippedDictionary = new Dictionary(
        new Pair('z', 'a'),
        new Pair('y', 'b'),
        new Pair('x', 'c'),
    );

    expect($dictionary->flip())->toEqual($expectedFlippedDictionary);
});

it('creates a dictionary of the items are present in all the other collections', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 34),
        new Pair('d', 4),
        new Pair('e', 48),
        new Pair('f', 5),
        new Pair('g', 10),
    );
    $list = new ArrayList([4, 5, 6, 7, 8]);
    $otherDictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1), new Pair(3, 5), new Pair(4, 4));
    $set = new Set([34, 5, 10, 4]);

    $expected = new Dictionary(new Pair('d', 4), new Pair('f', 5));

    expect($dictionary->intersect($list, $otherDictionary, $set))->toEqual($expected);
});

it('creates a new dictionary by applying the provided callback to each items in the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair(0, 'a'),
        new Pair(1, 'b'),
        new Pair(2, 'c'),
        new Pair(3, 'd'),
        new Pair(4, 'e'),
    );
    $callback = static fn (string $value, int $key) => $key . strtoupper($value);

    $expectedDictionary = new Dictionary(
        new Pair(0, '0A'),
        new Pair(1, '1B'),
        new Pair(2, '2C'),
        new Pair(3, '3D'),
        new Pair(4, '4E'),
    );

    expect($dictionary->map($callback))->toEqual($expectedDictionary);
});

it('merges the items from all collections into a new dictionary', function () {
    $dictionary = new Dictionary(new Pair(0, 'b'), new Pair(23, 'z'), new Pair(4, 'x'));
    $list = new ArrayList(['a', 'b', 'c']);
    $set = new Set(['g', 'h', 'c']);

    $expectedDictionary = new Dictionary(
        new Pair(0, 'g'),
        new Pair(23, 'z'),
        new Pair(4, 'x'),
        new Pair(1, 'h'),
        new Pair(2, 'c'),
    );

    expect($dictionary->merge($list, $set))->toEqual($expectedDictionary);
});

it('applies the given selector to all elements in the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );
    $selector = static fn (int $value, string $key) => $key . $value;

    $expectedDictionary = new Dictionary(
        new Pair('a', 'a1'),
        new Pair('b', 'b2'),
        new Pair('c', 'c3'),
    );

    $dictionary->apply($selector);

    expect($dictionary)->toEqual($expectedDictionary);
});

it('checks whether the given value is present in the dictionary or not', function (Dictionary $dictionary, mixed $value, bool $expectedResult) {
    expect($dictionary->contains($value))->toBe($expectedResult);
})->with([
    [new Dictionary(new Pair('a', 1), new Pair('b', 2)), 1, true],
    [new Dictionary(new Pair('a', 1), new Pair('b', 2)), 3, false],
    [new Dictionary(new Pair('a', 1), new Pair('b', 2)), 'a', false],
    [new Dictionary(new Pair('a', 1), new Pair('b', 2)), 'b', false],
]);

it('it returns a new dictionary containing all elements from the original dictionary matching the given selector', function (Dictionary $originalDictionary, callable $selector, Dictionary $expectedDictionary) {
    $newDictionary = $originalDictionary->filter($selector);
    expect($newDictionary)->toEqual($expectedDictionary);
})->with([
    [
        new Dictionary(
            new Pair('a', 1),
            new Pair('b', 2),
            new Pair('c', 3),
            new Pair('d', 4),
            new Pair('e', 5),
        ),
        static fn (int $value, string $key) => $value % 2 === 0,
        new Dictionary(
            new Pair('b', 2),
            new Pair('d', 4),
        ),
    ],
    [
        new Dictionary(
            new Pair('a', 1),
            new Pair('b', 2),
            new Pair('c', 3),
            new Pair('d', 4),
            new Pair('e', 5),
        ),
        static fn (int $value, string $key) => $value % 2 !== 0,
        new Dictionary(
            new Pair('a', 1),
            new Pair('c', 3),
            new Pair('e', 5),
        ),
    ],
]);

it('throws exception when trying to invoke method which could not be invoked on empty dictionary', function (string $method, array $args) {
    $dictionary = new Dictionary();
    $dictionary->{$method}(...$args);
})->with([
    ['pop', []],
    ['random', [1]],
    ['secureRandom', [1]],
    ['shift', []],
])->throws(UnderflowException::class, 'The collection is empty.');

it('removes and returns the last item of the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $expectedDictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
    );

    $popped = $dictionary->pop();

    expect($popped)->toEqual(3)->and($dictionary)->toEqual($expectedDictionary);
});

it('throws exception when trying to get more random elements from dictionary than it has', function (string $method) {
    $dictionary = new Dictionary(new Pair('a', 1), new Pair('b', 2));
    $dictionary->{$method}(3);
})->with([
    ['random'],
    ['secureRandom'],
])->throws(OutOfRangeException::class);

it('returns $count random elements from the dictionary', function (string $method) {
    $dictionary = new Dictionary(new Pair('a', 1), new Pair('b', 2), new Pair('c', 3));
    $random = $dictionary->{$method}(2);

    expect($random)->toBeInstanceOf(Dictionary::class)
        ->and($random->count())->toBe(2);
    foreach ($random as $key => $value) {
        expect($dictionary[$key])->toBe($value);
    }
})->with([
    ['random'],
    ['secureRandom'],
]);

it('reduces the dictionary to a single value by passing all of its elements to the given selector', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $result = $dictionary->reduce(static fn (int $carry, int $value) => $carry + $value, 0);

    expect($result)->toBe(6);
});

it('returns a list of the keys to which a value is assigned which passes the given selector', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
        new Pair('d', 4),
        new Pair('e', 5),
    );

    $expectedKeys = new ArrayList(['b', 'd']);

    $keys = $dictionary->searchAll(static fn (int $value) => $value % 2 === 0);

    expect($keys)->toEqual($expectedKeys);
});

it('removes and returns the first item of the dictionary', function () {
    $dictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $expectedDictionary = new Dictionary(
        new Pair('b', 2),
        new Pair('c', 3),
    );

    $shifted = $dictionary->shift();

    expect($shifted)->toEqual(1)->and($dictionary)->toEqual($expectedDictionary);
});

it('sorts the dictionary into the given direction', function (SortDirection $direction, Dictionary $expectedDictionary) {
    $dictionary = new Dictionary(new Pair('a', 1), new Pair('b', 3), new Pair('c', 2));
    $dictionary->sort($direction);
    expect($dictionary)->toEqual($expectedDictionary);
})->with([
    [
        SortDirection::ASCENDING,
        new Dictionary(new Pair('a', 1), new Pair('c', 2), new Pair('b', 3)),
    ],
    [
        SortDirection::DESCENDING,
        new Dictionary(new Pair('b', 3), new Pair('c', 2), new Pair('a', 1)),
    ]
]);

it('sorts the dictionary by the given selector', function () {
    $dictionary = new Dictionary(
        new Pair('b', 3),
        new Pair('c', 2),
        new Pair('a', 1),
    );

    $expectedDictionary = new Dictionary(
        new Pair('a', 1),
        new Pair('b', 3),
        new Pair('c', 2),
    );

    $dictionary->sortBy(static fn (Pair $a, Pair $b) => ord($a->key()) <=> ord($b->key()));

    expect($dictionary)->toEqual($expectedDictionary);
});

it('checks if the collection is not empty', function (Dictionary $dictionary, bool $expectedResult) {
    expect($dictionary->isNotEmpty())->toBe($expectedResult);
})->with([
    [new Dictionary(), false],
    [new Dictionary(new Pair('a', 1)), true],
]);
