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
     * @param object $object
     * @return bool
     */
    public function entityIsLtree(object $object): bool;

    /**
     * Check that ltree entity annotation is in the $className
     * @param string $className
     * @return bool
     */
    public function classIsLtree(string $className): bool;

    /**
     * Return children property reflection object
     *
     * @param object $object
     * @return ReflectionProperty
     */
    public function getChildrenProperty(object $object): ReflectionProperty;

    /**
     * Return parent property reflection object
     *
     * @param object $object
     * @return ReflectionProperty
     */
    public function getParentProperty(object $object): ReflectionProperty;

    /**
     * Return path property reflection object
     *
     * @param object $object
     * @return ReflectionProperty
     */
    public function getPathProperty(object $object): ReflectionProperty;
}
