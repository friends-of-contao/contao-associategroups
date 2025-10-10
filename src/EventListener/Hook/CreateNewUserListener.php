<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\EventListener\Hook;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

use function time;

#[AsHook('createNewUser')]
final readonly class CreateNewUserListener
{
    public function __construct(private Connection $connection)
    {
    }

    /** @param array<string, mixed> $data */
    public function __invoke(int $userId, array $data): void
    {
        $groups = StringUtil::deserialize($data['groups'], true);
        if ($groups === []) {
            return;
        }

        foreach ($groups as $group) {
            $this->connection->insert(
                'tl_member_to_group',
                [
                    'tstamp'    => time(),
                    'member_id' => $userId,
                    'group_id'  => $group,
                ],
            );
        }
    }
}
