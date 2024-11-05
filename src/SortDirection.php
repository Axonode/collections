<?php

declare(strict_types=1);

namespace Axonode\Collections;

enum SortDirection
{
    case ASCENDING;
    case DESCENDING;

    public function getMultiplier(): int
    {
        return match ($this) {
            self::ASCENDING => 1,
            self::DESCENDING => -1,
        };
    }
}
