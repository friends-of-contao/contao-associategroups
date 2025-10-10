<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use FriendsOfContao\AssociateGroups\Controller\SyncGroupsController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function substr;

#[AsCallback('tl_member', 'config.onload')]
#[AsCallback('tl_user', 'config.onload')]
final readonly class SyncOperationListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function __invoke(DataContainer $dataContainer): void
    {
        $GLOBALS['TL_DCA'][$dataContainer->table]['list']['global_operations']['sync_associate_groups']['href']
            = $this->urlGenerator->generate(SyncGroupsController::class, ['type' => substr($dataContainer->table, 3)]);
    }
}
