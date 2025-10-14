<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\StringUtil;
use FriendsOfContao\AssociateGroups\AssociateGroups;

use function substr;

#[AsCallback('tl_member', 'config.onsubmit')]
#[AsCallback('tl_user', 'config.onsubmit')]
final readonly class UpdateGroupsListener
{
    public function __construct(private AssociateGroups $associateGroups)
    {
    }

    public function __invoke(FrontendUser|DataContainer $dataContainer): void
    {
        if ($dataContainer instanceof FrontendUser) {
            $groups = StringUtil::deserialize($dataContainer->groups, true);

            /** @psalm-suppress ArgumentTypeCoercion */
            $this->associateGroups->updateGroups('member', $dataContainer->id, $groups);

            return;
        }

        $currentRecord = $dataContainer->getCurrentRecord();
        if ($currentRecord === null) {
            return;
        }

        $type   = substr($dataContainer->table, 3);
        $groups = StringUtil::deserialize($currentRecord['groups'], true);

        /** @psalm-suppress ArgumentTypeCoercion */
        $this->associateGroups->updateGroups($type, (int) $currentRecord['id'], $groups);
    }
}
