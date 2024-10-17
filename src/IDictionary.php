<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * @template TKey of array-key|object
 * @template TValue
 *
 * @extends ICollection<TKey, TValue>
 */
interface IDictionary extends ICollection
{
}
