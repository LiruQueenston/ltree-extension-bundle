<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\TreeBuilder;

use DDL\LtreeExtensionBundle\TreeBuilder\Exceptions\NotImplementException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class TreeBuilderFromObjectResult implements TreeBuilderInterface
{
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTree($list, string $pathName, array $parentPath = [], ?string $parentName = null, ?string $childrenName = null)
    {
        throw new NotImplementException('Build tree from object not implement yet');
    }
}
