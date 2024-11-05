<?php

declare(strict_types=1);

use Axonode\Collections\ArrayList;
use Axonode\Collections\Dictionary;
use Axonode\Collections\Pair;
use Axonode\Collections\Set;
use Axonode\Collections\SortDirection;

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

it('chunks itself into lists with given length', function () {
    $list = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    $length = 3;
    $expectedChunks = new ArrayList([new ArrayList([1, 2, 3]), new ArrayList([4, 5, 6]), new ArrayList([7, 8, 9]), new ArrayList([10])]);

    expect($list->chunk($length))->toEqual($expectedChunks);
});

it('returns a list of the keys from the list', function () {
    $list = new ArrayList([1, 2, 3]);
    $expectedKeys = new Set([0, 1, 2]);

    expect($list->keys())->toEqual($expectedKeys);
});

it('returns a list of values from the list', function () {
    $list = new ArrayList([1, 2, 3]);
    $expectedValues = new ArrayList([1, 2, 3]);

    expect($list->values())->toEqual($expectedValues)->not()->toBe($list);
});

it('counts how many times a value is in the list', function (ArrayList $list, Dictionary $expectedCount) {
    expect($list->countValues())->toEqual($expectedCount);
})->with([
    [new ArrayList(), new Dictionary()],
    [new ArrayList([1, 2, 3, 1, 2, 1]), new Dictionary(new Pair(1, 3), new Pair(2, 2), new Pair(3, 1))],
]);

it('creates a dictionary of the items not present in the other collections', function () {
    $list = new ArrayList([1, 2, 34, 4, 48, 5, 10]);
    $otherList = new ArrayList([4, 5, 6, 7, 8]);
    $dictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1));
    $set = new Set([34, 10]);

    $expectedDiff = new Dictionary(new Pair(1, 2), new Pair(4, 48));

    expect($list->diff($otherList, $dictionary, $set))->toEqual($expectedDiff);
});

it('creates a dictionary with flipped keys and values', function () {
    $list = new ArrayList(['a', 'b', 'c']);
    $expectedDictionary = new Dictionary(new Pair('a', 0), new Pair('b', 1), new Pair('c', 2));

    expect($list->flip())->toEqual($expectedDictionary);
});

it('creates a dictionary of the items are present in all the other collections', function () {
    $list = new ArrayList([1, 2, 34, 7, 4, 48, 5, 10]);
    $otherList = new ArrayList([4, 5, 6, 7, 8]);
    $dictionary = new Dictionary(new Pair(1, 1), new Pair(2, 1), new Pair(3, 7), new Pair(4, 5));
    $set = new Set([34, 5, 10, 7]);

    $expected = new Dictionary(new Pair(3, 7), new Pair(6, 5));

    expect($list->intersect($otherList, $dictionary, $set))->toEqual($expected);
});

it('creates a new list by applying the provided callback to each items in the list', function () {
    $list = new ArrayList(['a', 'b', 'c', 'd', 'e']);
    $callback = static fn (string $value, int $key) => $key . strtoupper($value);

    $expectedList = new ArrayList(['0A', '1B', '2C', '3D', '4E']);

    expect($list->map($callback))->toEqual($expectedList);
});

it('merges the items from all collections into a new dictionary', function () {
    $list = new ArrayList(['a', 'b', 'c']);
    $dictionary = new Dictionary(new Pair(0, 'b'), new Pair(23, 'z'), new Pair(4, 'x'));
    $set = new Set(['g', 'h', 'c']);

    $expectedDictionary = new Dictionary(
        new Pair(0, 'g'),
        new Pair(1, 'h'),
        new Pair(2, 'c'),
        new Pair(23, 'z'),
        new Pair(4, 'x'),
    );

    expect($list->merge($dictionary, $set))->toEqual($expectedDictionary);
});

it('throws exception when trying to pad by negative value', function (string $method) {
    $list = new ArrayList([1, 2, 3]);

    $list->$method(-1, 0);
})->with([
    ['padLeft'],
    ['padRight'],
])->throws(OutOfRangeException::class, 'The length must be greater than 0.');

it('pads the list on the left', function () {
    $list = new ArrayList([1, 2, 3]);
    $length = 5;
    $value = 0;

    $expectedList = new ArrayList([0, 0, 1, 2, 3]);

    expect($list->padLeft($length, $value))->toEqual($expectedList);
});

it('pads the list on the right', function () {
    $list = new ArrayList([1, 2, 3]);
    $length = 5;
    $value = 0;

    $expectedList = new ArrayList([1, 2, 3, 0, 0]);

    expect($list->padRight($length, $value))->toEqual($expectedList);
});

it('it returns a new instance of itself', function () {
    $list = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    $result = $list->toList();

    expect($result)->toEqual($list)->not()->toBe($list);
});

it('it creates a set from itself', function () {
    $list = new ArrayList([1, 3, 5, 1, 4, 6, 3, 7, 3, 5, 10, 3, 2, 4]);
    $expectedSet = new Set([1, 3, 5, 4, 6, 7, 10, 2]);

    expect($list->toSet())->toEqual($expectedSet);
});

it('applies the given selector to all elements', function () {
    $list = new ArrayList(['a', 'b', 'c', 'd', 'e']);
    $callback = static fn (string $value) => strtoupper($value);
    $expectedList = new ArrayList(['A', 'B', 'C', 'D', 'E']);
    $list->apply($callback);

    expect($list)->toEqual($expectedList);
});

it('can check whether the given value is present in the list', function (ArrayList $list, mixed $value, bool $expectedResult) {
    expect($list->contains($value))->toBe($expectedResult);
})->with([
    [new ArrayList([1, 2, 3]), 2, true],
    [new ArrayList([1, 2, 3]), 4, false],
    [new ArrayList([1, 2, 3]), '2', false],
]);

it('returns a new list without the values did not pass the matching criteria', function () {
    $list = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);
    $callback = static fn (int $value) => $value % 2 === 0;

    $expectedList = new ArrayList([2, 4, 6, 8, 10]);

    expect($list->filter($callback))->toEqual($expectedList);
});

it('throws exception when trying to pop from empty list', function () {
    $list = new ArrayList();
    $list->pop();
})->throws(UnderflowException::class, 'The list is empty.');

it('pops the last element of the list', function () {
    $list = new ArrayList([1, 2, 3, 4, 5]);
    $expectedList = new ArrayList([1, 2, 3, 4]);
    $expectedValue = 5;

    expect($list->pop())->toBe($expectedValue)->and($list)->toEqual($expectedList);
});

it('pushes the given values to the end of the list', function () {
    $list = new ArrayList([1, 2, 3]);
    $expectedList = new ArrayList([1, 2, 3, 4, 5, 6]);

    $list->push(4, 5, 6);

    expect($list)->toEqual($expectedList);
});

it('throws exception when trying to retrieve random element from empty list', function () {
    $list = new ArrayList();
    $list->random();
})->throws(UnderflowException::class, 'The list is empty.');

it('throws exception when trying to retrieve more random elements from the list than it contains', function (string $method) {
    $list = new ArrayList([1, 2, 3]);
    $list->$method(4);
})->with([
    ['random'],
    ['secureRandom']
])->throws(OutOfRangeException::class, 'The count must be less than or equal to the number of elements in the list.');

it('returns a dictionary of $count random elements from the list', function () {
    $list = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

    $result = $list->random(3);

    expect($result->count())->toBe(3);
    foreach ($result as $value) {
        expect($list->contains($value))->toBeTrue();
    }
});

it('returns a dictionary of $count securely random elements from the list', function () {
    $list = new ArrayList([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]);

    $result = $list->secureRandom(3);

    expect($result->count())->toBe(3);
    foreach ($result as $value) {
        expect($list->contains($value))->toBeTrue();
    }
});

it('reduces the list to a single value with the given selector and initial value', function () {
    $list = new ArrayList([1, 2, 3, 4, 5]);
    $callback = static fn (int $carry, int $value) => $carry + $value;
    $initial = 0;

    expect($list->reduce($callback, $initial))->toBe(15);
});

it('returns the offset of the first occurrence of the searched value if found', function (ArrayList $list, mixed $value, ?int $expectedResult) {
    expect($list->search($value))->toBe($expectedResult);
})->with([
    [new ArrayList([1, 2, 3, 4, 5]), 3, 2],
    [new ArrayList([1, 2, 3, 4, 5]), 6, null],
]);

it('returns a list of the offsets of the occurrences of the searched element', function (ArrayList $list, mixed $value, ArrayList $expectedResult) {
    expect($list->searchAll(fn ($v) => $v === $value))->toEqual($expectedResult);
})->with([
    [new ArrayList([1, 2, 3, 4, 5, 3, 6, 3, 7, 3, 8]), 3, new ArrayList([2, 5, 7, 9])],
    [new ArrayList([1, 2, 3, 4, 5, 3, 6, 3, 7, 3, 8]), 6, new ArrayList([6])],
    [new ArrayList([1, 2, 3, 4, 5, 3, 6, 3, 7, 3, 8]), 9, new ArrayList()],
]);

it('throws exception when trying to shift from empty list', function () {
    $list = new ArrayList();
    $list->shift();
})->throws(UnderflowException::class, 'The list is empty.');

it('removes the first element of the list and return it', function () {
    $list = new ArrayList([1, 2, 3, 4, 5]);
    $expectedList = new ArrayList([2, 3, 4, 5]);
    $expectedValue = 1;

    expect($list->shift())->toBe($expectedValue)->and($list)->toEqual($expectedList);
});

it('sorts the list in the given order', function (ArrayList $list, SortDirection $direction, ArrayList $expectedList) {
    expect($list->sort($direction))->toEqual($expectedList);
})->with([
    [new ArrayList([2, 3, 1]), SortDirection::ASCENDING, new ArrayList([1, 2, 3])],
    [new ArrayList([2, 1, 3]), SortDirection::DESCENDING, new ArrayList([3, 2, 1])],
]);

it('sorts the list by the given selector', function () {
    $list = new ArrayList([5, 3, 4]);
    $selector = static fn (int $a, int $b) => $a <=> $b;
    $expectedList = new ArrayList([3, 4, 5]);

    expect($list->sortBy($selector))->toEqual($expectedList);
});

