<?php

declare(strict_types=1);

namespace Axonode\Collections\Traits;

use Axonode\Collections\Contracts\ICollection;
use Axonode\Collections\Contracts\IDictionary;
use Axonode\Collections\Contracts\IList;

/**
 * @internal
 */
trait CollectionShared
{
    public function toList(): IList
    {
        return $this->values();
    }

    public function merge(ICollection ...$collections): IDictionary
    {
        $merged = $this->toDictionary();

        foreach ($collections as $collection) {
            foreach ($collection as $key => $value) {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    public function any(callable $selector): bool
    {
        foreach ($this->values() as $value) {
            if ($selector($value)) {
                return true;
            }
        }

        return false;
    }

    public function every(callable $selector): bool
    {
        foreach ($this->values() as $value) {
            if (!$selector($value)) {
                return false;
            }
        }

        return true;
    }
}
