<?php

declare(strict_types=1);

namespace Axonode\Collections;

/**
 * Represents a collection of items.
 *
 * @template TKey of array-key|object
 * @template TValue
 *
 * @extends \ArrayAccess<TKey, TValue>
 * @extends \Iterator<TKey, TValue>
 */
interface ICollection extends \ArrayAccess, \Iterator, \Countable
{
}
