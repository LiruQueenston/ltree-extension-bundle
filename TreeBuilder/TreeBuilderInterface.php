<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\TreeBuilder;

use Countable;
use Traversable;

interface TreeBuilderInterface
{
    /**
     * @param mixed[]|Traversable|Countable|array $list
     * @param string $pathName name of path property
     * @param mixed[] $parentPath path from parent entity
     * @param string|null $parentName parent property name
     * @param string|null $childrenName child property name
     *
     * @return mixed[]|object
     */
    public function buildTree($list, string $pathName, array $parentPath = [], ?string $parentName = null, ?string $childrenName = null);
}
