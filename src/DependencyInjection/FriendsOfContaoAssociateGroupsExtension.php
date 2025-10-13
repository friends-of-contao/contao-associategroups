<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\DependencyInjection;

use Override;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class FriendsOfContaoAssociateGroupsExtension extends Extension
{
    /** {@inheritDoc} */
    #[Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config'),
        );

        $loader->load('services.yaml');
    }
}
