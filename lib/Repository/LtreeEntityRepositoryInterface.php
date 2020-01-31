<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Repository;

use DDL\LtreeExtensionBundle\Annotation\Driver\AnnotationDriverInterface;
use DDL\LtreeExtensionBundle\TreeBuilder\TreeBuilderInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

interface LtreeEntityRepositoryInterface
{
    public function setTreeBuilder(TreeBuilderInterface $treeBuilder): void;

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void;

    public function setAnnotationDriver(AnnotationDriverInterface $annotationDriver): void;

    /**
     * @param object $entity object entity
     */
    public function getAllParentQueryBuilder(object $entity): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     *
     * @return mixed[] with parents for $entity. The root node is last
     */
    public function getAllParent(object $entity, int $hydrate = Query::HYDRATE_OBJECT): array;

    /**
     * @param object $entity object entity
     */
    public function getAllChildrenQueryBuilder(object $entity): QueryBuilder;

    /**
     * @param object $entity object entity
     * @param bool $treeMode This flag set how result will be presented
     * @param int $hydrate Doctrine processing mode to be used during hydration process.
     *                               One of the Query::HYDRATE_* constants.
     *
     * @return mixed[] If $treeMode is true, result will be grouped to tree.
     *                  If hydrate is object, children placed in children property.
     *                  If hydrate is array, children placed in __children key.
     *               If $treeMode is false, result will be in one level array
     */
    public function getAllChildren(object $entity, bool $treeMode = false, int $hydrate = Query::HYDRATE_OBJECT): array;

    /**
     * @param object $entity object entity
     * @param object|mixed[] $to object or path array
     */
    public function moveNode(object $entity, $to): void;
}
