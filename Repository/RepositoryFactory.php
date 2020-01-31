<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Repository;

use DDL\LtreeExtensionBundle\Annotation\Driver\AnnotationDriverInterface;
use DDL\LtreeExtensionBundle\TreeBuilder\TreeBuilderInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function assert;
use function is_a;
use function ltrim;

class RepositoryFactory implements \Doctrine\ORM\Repository\RepositoryFactory
{
    /**
     * The list of EntityRepository instances.
     *
     * @var ObjectRepository[]
     */
    protected $repositoryList = [];

    /** @var AnnotationDriverInterface */
    protected $annotationDriver;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var TreeBuilderInterface */
    protected $treeBuilder;

    public function __construct(
        AnnotationDriverInterface $annotationDriver,
        PropertyAccessorInterface $propertyAccessor,
        TreeBuilderInterface $treeBuilder
    ) {
        $this->annotationDriver = $annotationDriver;
        $this->propertyAccessor = $propertyAccessor;
        $this->treeBuilder = $treeBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $entityName = ltrim($entityName, '\\');

        if (isset($this->repositoryList[$entityName])) {
            return $this->repositoryList[$entityName];
        }

        $repository = $this->createRepository($entityManager, $entityName);

        $this->repositoryList[$entityName] = $repository;

        return $repository;
    }

    /**
     * Create a new repository instance for an entity class.
     *
     * @param EntityManagerInterface $entityManager The EntityManager instance.
     * @param string                               $entityName    The name of the entity.
     */
    protected function createRepository(EntityManagerInterface $entityManager, string $entityName): ObjectRepository
    {
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName;

        if ($repositoryClassName === null) {
            $configuration       = $entityManager->getConfiguration();
            $repositoryClassName = $configuration->getDefaultRepositoryClassName();
        }

        $repo = new $repositoryClassName($entityManager, $metadata);

        if ($repo instanceof LtreeEntityRepositoryInterface) {
            $repo->setAnnotationDriver($this->annotationDriver);
            $repo->setPropertyAccessor($this->propertyAccessor);
            $repo->setTreeBuilder($this->treeBuilder);
        }

        assert(is_a($repo, ObjectRepository::class));

        return $repo;
    }
}
