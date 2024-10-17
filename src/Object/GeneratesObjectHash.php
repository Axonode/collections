<?php

declare(strict_types=1);

namespace Axonode\Collections\Object;

trait GeneratesObjectHash
{
    public function getHash(): string
    {
        return spl_object_hash($this);
    }
}
