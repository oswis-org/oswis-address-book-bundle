<?php

namespace OswisOrg\OswisAddressBookBundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class OswisOrgOswisAddressBookExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param  array  $configs
     * @param  ContainerBuilder  $container
     *
     * @throws Exception
     */
    final public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');
        $configuration = $this->getConfiguration($configs, $container);
        if ($configuration) {
            $config = $this->processConfiguration($configuration, $configs);
            $this->oswisAddressBookSettingsProvider($container, $config);
        }
    }

    /**
     * @param  ContainerBuilder  $container
     * @param  array  $config
     *
     * @throws ServiceNotFoundException
     */
    private function oswisAddressBookSettingsProvider(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('oswis_org_oswis_address_book.oswis_address_book_settings_provider');
        $definition->setArgument(0, $config['primary']);
    }

    final public function prepend(ContainerBuilder $container): void
    {
    }
}
