<?php

declare(strict_types=1);

use Axonode\Collections\Set;

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
