<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Hook;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;

use FriendsOfContao\AssociateGroups\AssociateGroups;

use function time;

#[AsHook('createNewUser')]
final readonly class CreateNewUserListener
{
    public function __construct(private AssociateGroups $associateGroups)
    {
    }

    /** @param array<string, mixed> $data */
    public function __invoke(int $userId, array $data): void
    {
        $groups = StringUtil::deserialize($data['groups'], true);
        if ($groups === []) {
            return;
        }

        $this->associateGroups->updateGroups('member', $userId, $groups);
    }
}
