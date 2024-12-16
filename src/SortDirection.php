<?php

declare(strict_types=1);

namespace Axonode\Collections;

enum SortDirection: int
{
    case ASCENDING = 1;
    case DESCENDING = -1;
}
