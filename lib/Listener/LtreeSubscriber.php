<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Listener;

use DDL\LtreeExtensionBundle\Annotation\Driver\AnnotationDriverInterface;
use DDL\LtreeExtensionBundle\Repository\LtreeEntityRepositoryInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use ErrorException;
use LogicException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use function array_push;
use function get_class;
use function is_array;
use function reset;
use function sprintf;

class LtreeSubscriber implements EventSubscriber
{
    /** @var AnnotationDriverInterface */
    protected $annotationDriver;
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    public function __construct(AnnotationDriverInterface $annotationDriver, PropertyAccessorInterface $propertyAccessor)
    {
        $this->annotationDriver = $annotationDriver;
        $this->propertyAccessor = $propertyAccessor;
    }

    protected function buildPath(object $entity, ClassMetadata $classMetadata): void
    {
        $pathName = $this->annotationDriver->getPathProperty($entity)->getName();
        $parentName = $this->annotationDriver->getParentProperty($entity)->getName();

        $parent = $this->propertyAccessor->getValue($entity, $parentName);
        $identifiers = $classMetadata->getIdentifierValues($entity);
        $idValue = reset($identifiers);

        if (! $idValue) {
            throw new LogicException("Can't build path property without id");
        }

        $pathValue = [];
        if ($parent) {
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            if (! $pathValue || empty($pathValue)) {
                $this->buildPath($parent, $classMetadata);
                $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
            }

            if (! $pathValue || empty($pathValue)) {
                throw new ErrorException('Unable to build parent path property');
            }
        }

        if (! is_array($pathValue)) {
            $this->buildPath($parent, $classMetadata);
            $pathValue = $this->propertyAccessor->getValue($parent, $pathName);
        }

        array_push($pathValue, $idValue);
        $this->propertyAccessor->setValue($entity, $pathName, $pathValue);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getEntity();
        if (! $this->annotationDriver->entityIsLtree($entity)) {
            return;
        }

        $parentPath = $this->annotationDriver->getParentProperty($entity)->getName();
        if (! $args->hasChangedField($parentPath)) {
            return;
        }

        $repo = $args->getEntityManager()->getRepository(get_class($entity));
        if (! $repo instanceof LtreeEntityRepositoryInterface) {
            throw new LogicException(sprintf('%s must implement LtreeEntityRepositoryInterface', get_class($repo)));
        }

        $repo->moveNode($entity, $args->getNewValue($parentPath));
        $this->buildPath($entity, $args->getEntityManager()->getClassMetadata(get_class($entity)));
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (! $this->annotationDriver->entityIsLtree($entity)) {
                continue;
            }

            $classMetadata = $em->getClassMetadata(get_class($entity));
            $this->buildPath($entity, $classMetadata);
            $uow->recomputeSingleEntityChangeSet($classMetadata, $entity);
        }
    }

    /**
     * @{@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            'preUpdate',
            'onFlush',
        ];
    }
}
