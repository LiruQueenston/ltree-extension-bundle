<?php

declare(strict_types=1);

namespace DDL\LtreeExtensionBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class LtreeExtensionExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $dbalConfig = [
            'dbal' => [
                'types' => ['ltree' => 'DDL\LtreeExtensionBundle\Types\LTreeType'],
                'mapping_types' => ['ltree' => 'ltree'],
            ],
            'orm' => [
                'repository_factory'=>'ddl_ltreeextensionbundle.repository_factory',
                'dql' => [
                    'string_functions' => [
                        'ltree_concat' => 'DDL\LtreeExtensionBundle\DqlFunction\LtreeConcatFunction',
                        'ltree_subpath' => 'DDL\LtreeExtensionBundle\DqlFunction\LtreeSubpathFunction',
                    ],
                    'numeric_functions' => [
                        'ltree_nlevel' => 'DDL\LtreeExtensionBundle\DqlFunction\LtreeNlevelFunction',
                        'ltree_operator' => 'DDL\LtreeExtensionBundle\DqlFunction\LtreeOperatorFunction',
                    ],
                ],
            ],
        ];

        $container->prependExtensionConfig('doctrine', $dbalConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
