<?php

declare(strict_types=1);

use Axonode\Collections\ArrayList;

it('creates a list of the given items', function (array $initialItems, ArrayList $expectedList) {
    expect(new ArrayList($initialItems))->toEqual($expectedList);
})->with([
    [[], new ArrayList()],
    [[1, 2, 3, 4], new ArrayList([1, 2, 3, 4])],
    [['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4], new ArrayList([1, 2, 3, 4])]
]);

it('can loop through values', function () {
    $list = new ArrayList(['abc', 'def', 'ghi']);
    $expectedValues = ['abc', 'def', 'ghi'];

    $expectedKey = 0;
    foreach ($list as $actualKey => $actualValue) {
        expect($expectedKey)->toBe($actualKey)
            ->and($expectedValues[$expectedKey])->toBe($actualValue);

        $expectedKey++;
    }
});

it('returns if a offset exists in the list by calling the method directly', function (ArrayList $list, int $offset, bool $expectedResult) {
    expect($list->offsetExists($offset))->toBe($expectedResult);
})->with([
    [new ArrayList(), 1, false],
    [new ArrayList(['a', 'b', 'c']), 3, false],
    [new ArrayList(['a', 'b', 'c']), 2, true],
]);

it('returns if a offset exists in the list by calling passing it to isset', function (ArrayList $list, int $offset, bool $expectedResult) {
    expect(isset($list[$offset]))->toBe($expectedResult);
})->with([
    [new ArrayList(), 1, false],
    [new ArrayList(['a', 'b', 'c']), 3, false],
    [new ArrayList(['a', 'b', 'c']), 2, true],
]);

it('throws exception when trying to retrieve non-existing item by calling the method directly', function (ArrayList $list, int $offset) {
    $list->offsetGet($offset);
})->with([
    [new ArrayList(), 4],
    [new ArrayList(['a', 'b']), 4]
])->throws(OutOfBoundsException::class, 'The specified key (4) does not exist.');

it('throws exception when trying to retrieve non-existing item by array syntax', function (ArrayList $list, int $offset) {
    $_ = $list[$offset];
})->with([
    [new ArrayList(), 4],
    [new ArrayList(['a', 'b']), 4]
])->throws(OutOfBoundsException::class, 'The specified key (4) does not exist.');

it('returns the item at the given offset by calling the method directly', function () {
    $list = new ArrayList(['a', 'b', 'c']);

    expect($list->offsetGet(1))->toBe('b');
});

it('returns the item at the given offset by array syntax', function () {
    $list = new ArrayList(['a', 'b', 'c']);

    expect($list[1])->toBe('b');
});

it('returns the item by reference at the given offset', function () {
    $list = new ArrayList([1, 11, 111]);
    $list[1]++;

    expect($list->offsetGet(1))->toBe(12);
});

it('throws exception when trying to set offset out of range by calling the method directly', function (ArrayList $list, int $offset) {
    $list->offsetSet($offset, 'dummy-value');
})->with([
    [new ArrayList(), 1],
    [new ArrayList(['a', 'b']), 5]
])->throws(OutOfRangeException::class);

it('throws exception when trying to set offset out of range by array-syntax', function (ArrayList $list, int $offset) {
    $list[$offset] = 'dummy-value';
})->with([
    [new ArrayList(), 1],
    [new ArrayList(['a', 'b']), 5]
])->throws(OutOfRangeException::class);

it('sets the given value at the given offset by calling the method directly', function (ArrayList $list, int $offset, mixed $value) {
    $list->offsetSet($offset, $value);

    expect($list[$offset])->toBe($value);
})->with([
    [new ArrayList([]), 0, 'a'],
    [new ArrayList(['a', 'b']), 2, 'c'],
    [new ArrayList(['a', 'b']), 1, 'c'],
]);

it('sets the given value at the given offset by array-syntax', function (ArrayList $list, int $offset, mixed $value) {
    $list[$offset] = $value;

    expect($list[$offset])->toBe($value);
})->with([
    [new ArrayList([]), 0, 'a'],
    [new ArrayList(['a', 'b']), 2, 'c'],
    [new ArrayList(['a', 'b']), 1, 'c'],
]);

it('throws exception when trying to unset non-existing offset by calling the method directly', function (ArrayList $list, int $offset) {
    $list->offsetUnset($offset);
})->with([
    [new ArrayList(), 1],
    [new ArrayList(['a', 'b', 'c']), 3]
])->throws(OutOfBoundsException::class);

it('throws exception when trying to unset non-existing offset by passing it to unset', function (ArrayList $list, int $offset) {
    unset($list[$offset]);
})->with([
    [new ArrayList(), 1],
    [new ArrayList(['a', 'b', 'c']), 3]
])->throws(OutOfBoundsException::class);

it('removes the value at the given offset by calling the method directly', function () {
    $list = new ArrayList(['a', 'b', 'c']);
    $list->offsetUnset(1);

    expect($list)->toEqual(new ArrayList(['a', 'c']));
});

it('removes the value at the given offset by passing it to unset', function () {
    $list = new ArrayList(['a', 'b', 'c']);
    unset($list[1]);

    expect($list)->toEqual(new ArrayList(['a', 'c']));
});
