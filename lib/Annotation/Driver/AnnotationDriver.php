<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Annotation\Driver;

use Doctrine\Common\Annotations\Reader;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

class AnnotationDriver implements AnnotationDriverInterface
{
    /** @var  Reader */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    public function entityIsLtree(object $object): bool
    {
        return (bool) $this->getReader()->getClassAnnotation(new ReflectionObject($object), self::ENTITY_ANNOTATION);
    }

    public function classIsLtree(string $className): bool
    {
        return (bool) $this->getReader()->getClassAnnotation(new ReflectionClass($className), self::ENTITY_ANNOTATION);
    }

    public function getChildrenProperty(object $object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::CHILDREN_ANNOTATION);
    }

    public function getParentProperty(object $object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::PARENT_ANNOTATION);
    }

    public function getPathProperty(object $object): ReflectionProperty
    {
        return $this->findAnnotation($object, self::PATH_ANNOTATION);
    }

    public function getReader(): Reader
    {
        return $this->reader;
    }

    /**
     * @param object $object
     * @param string $annotationName
     * @return ReflectionProperty
     * @throws PropertyNotFoundException
     */
    protected function findAnnotation(object $object, string $annotationName): ReflectionProperty
    {
        $reflectionObject = new ReflectionObject($object);
        foreach ($reflectionObject->getProperties() as $property) {
            $result = $this->getReader()->getPropertyAnnotation($property, $annotationName);
            if ($result) {
                return $property;
            }
        }

        throw new PropertyNotFoundException($object, $annotationName);
    }
}
