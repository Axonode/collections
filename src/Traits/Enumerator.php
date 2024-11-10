<?php

declare(strict_types=1);

namespace Axonode\Collections\Traits;

/**
 * @internal
 */
trait Enumerator
{
    public function current(): mixed
    {
        return $this->valueAt($this->pointer);
    }

    public function key(): mixed
    {
        return $this->keyAt($this->pointer);
    }

    public function next(): void
    {
        ++$this->pointer;
    }

    public function rewind(): void
    {
        $this->pointer = 0;
    }

    public function valid(): bool
    {
        return $this->pointer >= 0 && $this->pointer < $this->count();
    }
}
