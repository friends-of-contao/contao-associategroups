<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use FriendsOfContao\AssociateGroups\AssociateGroups;
use Override;

final class SyncGroupsMigration extends AbstractMigration
{
    public function __construct(
        private readonly AssociateGroups $associateGroups,
        private readonly Connection $connection,
    ) {
    }

    #[Override]
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $tables        = [
            $this->associateGroups->associationTable('member'),
            $this->associateGroups->associationTable('user'),
        ];

        if (! $schemaManager->tablesExist($tables)) {
            return false;
        }

        return $this->associateGroups->isOutOfSync('member') || $this->associateGroups->isOutOfSync('user');
    }

    #[Override]
    public function run(): MigrationResult
    {
        foreach (['member', 'user'] as $type) {
            if (! $this->associateGroups->isOutOfSync($type)) {
                continue;
            }

            $this->associateGroups->sync($type);
        }

        return $this->createResult(true);
    }
}
