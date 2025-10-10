<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use FriendsOfContao\AssociateGroups\FriendsOfContaoAssociateGroupsBundle;
use Override;

final class Plugin implements BundlePluginInterface
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
}
