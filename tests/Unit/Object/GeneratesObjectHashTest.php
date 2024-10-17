<?php

declare(strict_types=1);

use Tests\Doubles\Object\UsesGeneratesObjectHash;

it('generates SPL object hash', function () {
    $object = new UsesGeneratesObjectHash();
    expect($object->getHash())->toBe(spl_object_hash($object));
});
