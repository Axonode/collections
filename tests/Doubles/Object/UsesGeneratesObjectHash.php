<?php

declare(strict_types=1);

namespace Tests\Doubles\Object;

use Axonode\Collections\Object\GeneratesObjectHash;
use Axonode\Collections\Object\Hashable;

final readonly class UsesGeneratesObjectHash implements Hashable
{
    use GeneratesObjectHash;
    
    public function __construct(
        public int $field = 123
    ) {}
}