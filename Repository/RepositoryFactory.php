<?php
/**
 * Created by PhpStorm.
 * User: levsemin
 * Date: 15.03.15
 * Time: 9:15
 */

namespace Slev\LtreeExtensionBundle\Repository;


use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Slev\LtreeExtensionBundle\Annotation\Driver\AnnotationDriverInterface;
use Slev\LtreeExtensionBundle\TreeBuilder\TreeBuilderInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class RepositoryFactory implements \Doctrine\ORM\Repository\RepositoryFactory
{
    /**
     * The list of EntityRepository instances.
     *
     * @var ObjectRepository[]
     */
    protected $repositoryList = [];

    /**
     * @var AnnotationDriverInterface
     */
    protected $annotationDriver;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * @var TreeBuilderInterface
     */
    protected $treeBuilder;

    function __construct(AnnotationDriverInterface $annotationDriver, PropertyAccessorInterface $propertyAccessor,
                         TreeBuilderInterface $treeBuilder)
    {
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
     *
     * @return ObjectRepository
     */
    protected function createRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata            = $entityManager->getClassMetadata($entityName);
        $repositoryClassName = $metadata->customRepositoryClassName;

        if ($repositoryClassName === null) {
            $configuration       = $entityManager->getConfiguration();
            $repositoryClassName = $configuration->getDefaultRepositoryClassName();
        }

        $repo = new $repositoryClassName($entityManager, $metadata);
        if ($repo instanceof LtreeEntityRepositoryInterface){
            $repo->setAnnotationDriver($this->annotationDriver);
            $repo->setPropertyAccessor($this->propertyAccessor);
            $repo->setTreeBuilder($this->treeBuilder);
        }
        return $repo;
    }
}