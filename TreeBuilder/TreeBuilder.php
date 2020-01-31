<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\TreeBuilder;

use LogicException;
use function is_array;
use function is_object;

class TreeBuilder implements TreeBuilderInterface
{
    /** @var  TreeBuilderInterface */
    protected $arrayBuilder;

    /** @var  TreeBuilderInterface */
    protected $objectBuilder;

    public function __construct(TreeBuilderInterface $arrayBuilder, TreeBuilderInterface $objectBuilder)
    {
        $this->arrayBuilder = $arrayBuilder;
        $this->objectBuilder = $objectBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTree($list, string $pathName, array $parentPath = [], ?string $parentName = null, ?string $childrenName = null)
    {
        $element = null;
        foreach ($list as $item) {
            $element = $item;
            break;
        }

        if (is_array($element)) {
            return $this->arrayBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        if (is_object($element)) {
            return $this->objectBuilder->buildTree($list, $pathName, $parentPath, $parentName, $childrenName);
        }

        throw new LogicException('Unable to find builder');
    }
}
