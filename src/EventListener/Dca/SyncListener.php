<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use FriendsOfContao\AssociateGroups\AssociateGroups;

use function explode;

#[AsCallback('tl_member_group', 'config.ondelete')]
#[AsCallback('tl_user_group', 'config.ondelete')]
final readonly class SyncListener
{
    public function __construct(private AssociateGroups $associateGroups)
    {
    }

    public function __invoke(DataContainer $dataContainer): void
    {
        $parts = explode('_', $dataContainer->table);
        if (! isset($parts[1])) {
            return;
        }

        $this->associateGroups->sync($parts[1]);
    }
}
