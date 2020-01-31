<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Annotation\Driver;

use ReflectionProperty;

interface AnnotationDriverInterface
{
    public const ENTITY_ANNOTATION = '\\DDL\\LtreeExtensionBundle\\Annotation\\LtreeEntity';
    public const CHILDREN_ANNOTATION = '\\DDL\\LtreeExtensionBundle\\Annotation\\LtreeChildren';
    public const PARENT_ANNOTATION = '\\DDL\\LtreeExtensionBundle\\Annotation\\LtreeParent';
    public const PATH_ANNOTATION = '\\DDL\\LtreeExtensionBundle\\Annotation\\LtreePath';

    /**
     * Check that ltree entity annotation is in the $object
     */
    public function entityIsLtree(object $object): bool;

    /**
     * Check that ltree entity annotation is in the $className
     */
    public function classIsLtree(string $className): bool;

    /**
     * Return children property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getChildrenProperty(object $object): ReflectionProperty;

    /**
     * Return parent property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getParentProperty(object $object): ReflectionProperty;

    /**
     * Return path property reflection object
     *
     * @throws PropertyNotFoundException
     */
    public function getPathProperty(object $object): ReflectionProperty;
}
