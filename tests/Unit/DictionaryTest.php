<?php

declare(strict_types=1);

use Axonode\Collections\Dictionary;
use Axonode\Collections\Object\Hashable;
use Axonode\Collections\Pair;

it('creates empty dictionary when no items provided', function () {
    $dictionary = new Dictionary();
    expect($dictionary->count())->toBe(0);
});

it('throws exception when incompatible offset provided', function () {
    $dictionary = new Dictionary();
    $dictionary->offsetExists([]);
})->throws(InvalidArgumentException::class, 'Unsupported offset type');

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
