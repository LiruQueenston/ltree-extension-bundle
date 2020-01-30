<?php

declare(strict_types=1);

namespace Slev\LtreeExtensionBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        return new TreeBuilder('slev_ltree_extension');
    }
}
