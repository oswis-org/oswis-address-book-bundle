<?php

namespace Zakjakub\OswisAddressBookBundle\DependencyInjection;

use RuntimeException;
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
        $treeBuilder = new TreeBuilder('zakjakub_oswis_address_book');
        $treeBuilder->getRootNode()->info('Default configuration for address book module for OSWIS (One Simple Web IS).')->end();

        return $treeBuilder;
    }
}
