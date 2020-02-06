<?php

namespace Zakjakub\OswisAddressBookBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     * @throws RuntimeException
     */
    final public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zakjakub_oswis_address_book', 'array');
        $rootNode = $treeBuilder->getRootNode();
        assert($rootNode instanceof ArrayNodeDefinition);
        $rootNode->info('Default configuration for address book module for OSWIS (One Simple Web IS).')->end();
        $this->addOrganizationConfig($rootNode);

        return $treeBuilder;
    }

    private function addOrganizationConfig(ArrayNodeDefinition $rootNode): void
    {
        $rootNode->children()->arrayNode('primary')->info('Slugs of primary entities.')->addDefaultsIfNotSet()->children()->scalarNode('organization')->info(
                'Primary organization.'
            )->defaultValue(null)->example('some-organization')->end();
    }
}
