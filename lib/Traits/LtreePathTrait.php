<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\Traits;

use DDL\LtreeExtensionBundle\Annotation\LtreePath;
use Doctrine\ORM\Mapping\Column;
use function count;

trait LtreePathTrait
{
    /**
     * @var mixed[]
     * @Column(type="ltree")
     * @LtreePath()
     */
    protected $ltreePath = null;

    /**
     * @return mixed[]
     */
    public function getLtreePath(): array
    {
        return $this->ltreePath;
    }

    /**
     * @param mixed[] $ltreePath
     */
    public function setLtreePath(array $ltreePath): void
    {
        $this->ltreePath = $ltreePath;
    }

    /**
     * Level number
     */
    public function getLevelNumber(): int
    {
        return count($this->getLtreePath());
    }
}
