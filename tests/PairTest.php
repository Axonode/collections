<?php

declare(strict_types=1);

use Axonode\Collections\Pair;

it('returns the key and value of the pair correctly', function () {
    $pair = new Pair(1, 'a');
    expect($pair->key())->toBe(1)
        ->and($pair->value())->toBe('a');
});

it('returns a new pair with the specified key', function () {
    $pair = new Pair(1, 'a');
    $newPair = $pair->withKey(2);
    expect($newPair->key())->toBe(2)
        ->and($newPair->value())->toBe('a');
});

it('returns a new pair with the specified value', function () {
    $pair = new Pair(1, 'a');
    $newPair = $pair->withValue('b');
    expect($newPair->key())->toBe(1)
        ->and($newPair->value())->toBe('b');
});
