<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use FriendsOfContao\AssociateGroups\FriendsOfContaoAssociateGroupsBundle;
use Override;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouteCollection;

final class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    /** {@inheritDoc} */
    #[Override]
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(FriendsOfContaoAssociateGroupsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setReplace(['associategroups']),
        ];
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[Override]
    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel): RouteCollection|null
    {
        $loader = $resolver->resolve(__DIR__ . '/../../config/routing.yaml');
        if ($loader === false) {
            return null;
        }

        return $loader->load(__DIR__ . '/../../config/routing.yaml');
    }
}
