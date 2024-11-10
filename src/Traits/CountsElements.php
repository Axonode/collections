<?php

declare(strict_types=1);

namespace Axonode\Collections\Traits;

/**
 * @internal
 */
trait CountsElements
{
    public function count(): int
    {
        return count($this->values);
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function isNotEmpty(): bool
    {
        return !$this->isEmpty();
    }

    private function assertIsNotEmpty(): void
    {
        if ($this->isEmpty()) {
            throw new \UnderflowException('The collection is empty.');
        }
    }
}
