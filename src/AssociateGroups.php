<?php

declare(strict_types=1);

namespace FriendsOfContao\AssociateGroups;

use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Exception;

use function array_diff;
use function array_map;
use function array_unique;
use function array_values;
use function time;

final readonly class AssociateGroups
{
    public function __construct(private Connection $connection)
    {
    }

    public function sync(string $type): void
    {
        $table         = $this->sourceTable($type);
        $schemaManager = $this->connection->createSchemaManager();
        $fields        = $schemaManager->listTableColumns($table);

        if (! isset($fields['groups'])) {
            throw new Exception('Can not handle ' . $type . ' for group association sync');
        }

        $transaction = function () use ($type, $table): void {
            $this->cleanTable($type);

            $result = $this->connection->createQueryBuilder()
                ->select('id, groups')
                ->from($table)
                ->where('groups != \'\'')
                ->executeQuery();

            while ($row = $result->fetchAssociative()) {
                /** @psalm-suppress ArgumentTypeCoercion */
                $groups = $this->uniqueGroups(StringUtil::deserialize($row['groups'], true));
                if ($groups === []) {
                    continue;
                }

                $this->insertRows($type, (int) $row['id'], $groups);
            }
        };

        $this->connection->transactional($transaction);
    }

    public function isOutOfSync(string $type): bool
    {
        $source = $this->connection->createQueryBuilder()
            ->select('r.id, r.groups')
            ->from($this->sourceTable($type))
            ->where('groups != \'\'')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($source as $row) {
            $associations = $this->connection->createQueryBuilder()
                ->select('group_id')
                ->from($this->sourceTable($type))
                ->where($type . '_id = :userId')
                ->setParameter('userId', $row['id'])
                ->executeQuery()
                ->fetchFirstColumn();

            /** @psalm-suppress ArgumentTypeCoercion */
            $groups = $this->uniqueGroups(StringUtil::deserialize($row['groups'], true));
            if (array_diff($groups, $associations) !== []) {
                return true;
            }
        }

        return false;
    }

    public function deleteGroups(string $type, int $userId): void
    {
        $this->connection->delete($this->associationTable($type), [$type . '_id' => $userId]);
    }

    /** @param list<numeric-string|int> $groups */
    public function updateGroups(string $type, int $userId, array $groups): void
    {
        $transaction = function () use ($type, $userId, $groups): void {
            $groups    = $this->uniqueGroups($groups);
            $tableName = $this->associationTable($type);

            if ($groups === []) {
                $this->connection->delete($tableName, [$type . '_id' => $userId]);

                return;
            }

            $associations = $this->connection->createQueryBuilder()
                ->select('group_id')
                ->from($tableName)
                ->where($type . '_id = :userId')
                ->setParameter('userId', $userId)
                ->executeQuery()
                ->fetchFirstColumn();

            $delete = array_diff($associations, $groups);
            $insert = array_values(array_diff($groups, $associations));

            if ($delete) {
                $this->connection->createQueryBuilder()
                    ->delete($tableName)
                    ->where($type . '_id = :userId')
                    ->andWhere('group_id IN (:groups)')
                    ->setParameter('userId', $userId)
                    ->setParameter('groups', $delete, ArrayParameterType::INTEGER)
                    ->executeStatement();
            }

            if (! $insert) {
                return;
            }

            $this->insertRows($tableName, $userId, $insert);
        };

        $this->connection->transactional($transaction);
    }

    private function sourceTable(string $type): string
    {
        return 'tl_' . $type;
    }

    public function associationTable(string $type): string
    {
        return $this->sourceTable($type) . '_to_group';
    }

    private function cleanTable(string $type): void
    {
        $this->connection->executeStatement('TRUNCATE TABLE ' . $this->associationTable($type));
    }

    /**
     * @param list<numeric-string|int> $groups
     *
     * @return list<int>
     */
    private function uniqueGroups(array $groups): array
    {
        return array_values(array_unique(array_map('intval', $groups)));
    }

    /** @param list<int> $groups */
    private function insertRows(string $type, int $userId, array $groups): void
    {
        foreach ($groups as $groupId) {
            $this->connection->insert(
                $this->associationTable($type),
                [
                    'tstamp'      => time(),
                    $type . '_id' => $userId,
                    'group_id'    => $groupId,
                ],
            );
        }
    }
}
