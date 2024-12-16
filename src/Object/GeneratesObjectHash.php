<?php

declare(strict_types=1);

namespace Axonode\Collections\Object;

/**
 * Provides a simple Hashable implementation for objects using the SPL extnesion.
 * @see Hashable
 */
trait GeneratesObjectHash
{
    public function getHash(): string
    {
        return spl_object_hash($this);
    }
}
