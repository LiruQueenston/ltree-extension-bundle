<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Repository;

use DDL\LtreeExtensionBundle\Annotation\Driver\AnnotationDriverInterface;
use DDL\LtreeExtensionBundle\TreeBuilder\TreeBuilderInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function array_keys;
use function array_values;
use function is_a;
use function sprintf;
use function str_replace;

class LtreeEntityRepository extends EntityRepository implements LtreeEntityRepositoryInterface
{
    /** @var AnnotationDriverInterface */
    private $annotationDriver=null;

    /** @var PropertyAccessorInterface */
    private $propertyAccessor=null;

    /** @var TreeBuilderInterface */
    private $treeBuilder=null;

    public function getTreeBuilder(): TreeBuilderInterface
    {
        if ($this->treeBuilder===null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }

        return $this->treeBuilder;
    }

    public function setTreeBuilder(TreeBuilderInterface $treeBuilder): void
    {
        $this->treeBuilder = $treeBuilder;
    }

    public function getPropertyAccessor(): PropertyAccessorInterface
    {
        if ($this->propertyAccessor===null) {
            throw new LogicException('Repository must inject property accessor service itself');
        }

        return $this->propertyAccessor;
    }

    public function setPropertyAccessor(PropertyAccessorInterface $propertyAccessor): void
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    public function getAnnotationDriver(): AnnotationDriverInterface
    {
        if ($this->annotationDriver===null) {
            throw new LogicException('Repository must inject annotation driver service itself');
        }

        return $this->annotationDriver;
    }

    public function setAnnotationDriver(AnnotationDriverInterface $annotationDriver): void
    {
        $this->annotationDriver = $annotationDriver;
    }

    protected function checkClass(object $entity): void
    {
        if (! is_a($entity, $this->getClassName())) {
            throw new InvalidArgumentException(sprintf('Entity must be instance of %s', $this->getClassName()));
        }

        if (! $this->getAnnotationDriver()->classIsLtree($this->getClassName())) {
            throw new InvalidArgumentException('Entity must have ltree entity annotation');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAllParentQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $aliasName = 'ltree_entity';
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);

        $qb = $this->createQueryBuilder($aliasName);
        $qb->where(sprintf("ltree_operator(%s.%s, '@>', :self_path)=true", $aliasName, $pathName));
        $qb->andWhere(sprintf('%s.%s<>:self_path', $aliasName, $pathName));
        $qb->orderBy(sprintf('%s.%s', $aliasName, $pathName), 'DESC');
        $qb->setParameter('self_path', $pathValue, 'ltree');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildrenQueryBuilder($entity): QueryBuilder
    {
        $this->checkClass($entity);
        $aliasName = 'ltree_entity';
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $orderFieldName = 'parent_paths_for_order';

        $qb = $this->createQueryBuilder($aliasName);
        $qb->addSelect(sprintf('ltree_subpath(%s.%s, 0, -1) as HIDDEN %s', $aliasName, $pathName, $orderFieldName));
        $qb->where(sprintf("ltree_operator(%s.%s, '<@', :self_path)=true", $aliasName, $pathName));
        $qb->andWhere(sprintf('%s.%s<>:self_path', $aliasName, $pathName));
        $qb->orderBy($orderFieldName);
        $qb->setParameter('self_path', $pathValue, 'ltree');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllParent($entity, $hydrate = Query::HYDRATE_OBJECT): array
    {
        return $this->getAllParentQueryBuilder($entity)->getQuery()->getResult($hydrate);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllChildren($entity, $treeMode = false, $hydrate = Query::HYDRATE_OBJECT): array
    {
        $this->checkClass($entity);
        $result = $this->getAllChildrenQueryBuilder($entity)->getQuery()->getResult($hydrate);
        if ($treeMode && $hydrate!==Query::HYDRATE_OBJECT && $hydrate!==Query::HYDRATE_ARRAY) {
            throw new LogicException('If treeMode is true, hydration mode must be object or array');
        }

        if (! $treeMode) {
            return $result;
        }

        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $pathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $parentName = $this->getAnnotationDriver()->getParentProperty($entity)->getName();
        $childName = $this->getAnnotationDriver()->getChildrenProperty($entity)->getName();

        return $this->treeBuilder->buildTree($result, $pathName, $pathValue, $parentName, $childName);
    }

    /**
     * {@inheritdoc}
     */
    public function moveNode($entity, $to): void
    {
        $this->checkClass($entity);
        $this->checkClass($to);

        $aliasName = 'ltree_entity';
        $pathName = $this->getAnnotationDriver()->getPathProperty($entity)->getName();
        $oldPathValue = $this->getPropertyAccessor()->getValue($entity, $pathName);
        $newPathValue = $this->getPropertyAccessor()->getValue($to, $pathName);

        $prepareString=static function ($str) use ($aliasName, $pathName) {
            $replacement = ['%alias%' => $aliasName, '%path%' => $pathName];

            return str_replace(array_keys($replacement), array_values($replacement), $str);
        };

        $qb = $this->createQueryBuilder($aliasName)
            ->update()
            ->set(
                $prepareString('%alias%.%path%'),
                $prepareString('ltree_concat(:new_path, ltree_subpath(%alias%.%path%, (ltree_nlevel(:self_path)-1)))')
            )
            ->where($prepareString("ltree_operator(%alias%.%path%, '<@', :self_path)=true"))
            ->setParameter(':self_path', $oldPathValue, 'ltree')
            ->setParameter(':new_path', $newPathValue, 'ltree');

        return $qb->getQuery()->execute();
    }
}
