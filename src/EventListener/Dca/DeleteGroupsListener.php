<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use FriendsOfContao\AssociateGroups\AssociateGroups;

use function substr;

#[AsCallback('tl_member', 'config.ondelete')]
#[AsCallback('tl_user', 'config.ondelete')]
final readonly class DeleteGroupsListener
{
    public function __construct(private AssociateGroups $associateGroups)
    {
    }

    public function __invoke(DataContainer $dataContainer): void
    {
        $this->associateGroups->deleteAssignedGroups(substr($dataContainer->table, 3), (int) $dataContainer->id);
    }
}
