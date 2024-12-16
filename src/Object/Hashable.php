<?php

declare(strict_types=1);

namespace Axonode\Collections\Object;

/**
 * Represents an object that can be uniquely identified by a hash.
 */
interface Hashable
{
    /**
     * @return string A unique hash with which an object could be identified.
     */
    public function getHash(): string;
}
