<?php

declare(strict_types=1);

namespace App\Utils;

use App\Entity\Product\ProductInputImage;
use Doctrine\ORM\PersistentCollection;
use LogicException;

class CollectionUtils
{
    /**
     * Reconstructs the original state of a OneToMany collection during onFlush.
     *
     * @param PersistentCollection<(int|string), object> $collection The collection being tracked by Doctrine.
     * @return array<int, ProductInputImage> The original items in the collection before the update.
     */
    public static function reconstructOriginalCollection(PersistentCollection $collection): array
    {
        /** @var list<ProductInputImage> $inserted */
        $inserted = $collection->getInsertDiff();

        /** @var list<ProductInputImage> $deleted */
        $deleted = $collection->getDeleteDiff();

        /** @var list<ProductInputImage> $current */
        $current = $collection->toArray();

        /** @var list<ProductInputImage> $original */
        $original = array_merge($current, $deleted);

        return array_udiff(
            $original,
            $inserted,
            fn ($a, $b) => self::compareById($a, $b)
        );
    }

    private static function compareById(object $a, object $b): int
    {
        if (!method_exists($a, 'getId') || !method_exists($b, 'getId')) {
            throw new LogicException('Entities must have getId() method for comparison.');
        }

        return $a->getId() <=> $b->getId();
    }
}
