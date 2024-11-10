<?php

declare(strict_types=1);

namespace Axonode\Collections\Traits;

use Axonode\Collections\Object\Hashable;

trait HashKeys
{
    private function toInternalKey(mixed $publicKey): string
    {
        return match (gettype($publicKey)) {
            'string' => $publicKey,
            'integer', 'resource', 'resource (closed)', 'double' => (string) $publicKey,
            'object' => $publicKey instanceof Hashable ? $publicKey->getHash() : spl_object_hash($publicKey),
            'NULL' => 'type::null',
            'array' => serialize($publicKey),
            'boolean' => $publicKey ? 'true' : 'false',
            default => throw new \TypeError('Invalid key type'),
        };
    }
}
