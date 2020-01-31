<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Annotation\Driver;

use Exception;
use function get_class;
use function sprintf;

class PropertyNotFoundException extends Exception
{
    /** @var string */
    private $className;

    /** @var string */
    private $annotationClassName;

    public function __construct(object $object, string $annotationClassName)
    {
        $this->className = get_class($object);
        $this->annotationClassName = $annotationClassName;

        parent::__construct(sprintf(
            'Class %s does not exist property annotated by %s',
            $this->getClassName(),
            $this->getAnnotationClassName()
        ));
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getAnnotationClassName(): string
    {
        return $this->annotationClassName;
    }
}
