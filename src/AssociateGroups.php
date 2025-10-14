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

        $this->cleanTable($type);

        $result = $this->connection->createQueryBuilder()
            ->select('id, groups')
            ->from($table)
            ->where('groups != \'\'')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($result as $row) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $this->updateGroups($type, (int) $row['id'], StringUtil::deserialize($row['groups'], true));
        }
    }

    public function isOutOfSync(string $type): bool
    {
        $source = $this->connection->createQueryBuilder()
            ->select('id, groups')
            ->from($this->sourceTable($type))
            ->where('groups != \'\'')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($source as $row) {
            $associations = $this->connection->createQueryBuilder()
                ->select('group_id')
                ->from($this->associationTable($type))
                ->where($type . '_id = :userId')
                ->setParameter('userId', $row['id'])
                ->executeQuery()
                ->fetchFirstColumn();

            /** @psalm-suppress ArgumentTypeCoercion */
            $groups = $this->uniqueGroups($type, StringUtil::deserialize($row['groups'], true));
            if (array_diff($groups, $associations) !== []) {
                return true;
            }
        }

        return false;
    }

    public function deleteAssignedGroups(string $type, int $userId): void
    {
        $this->connection->delete($this->associationTable($type), [$type . '_id' => $userId]);
    }

    /** @param list<numeric-string|int> $groups */
    public function updateGroups(string $type, int $userId, array $groups): void
    {
        $groups    = $this->uniqueGroups($type, $groups);
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

        $this->insertRows($type, $userId, $insert);
    }

    public function deleteGroup(string $type, int $groupId): void
    {
        $this->connection->createQueryBuilder()
            ->delete($this->associationTable($type))
            ->where('group_id = :groupId')
            ->setParameter('groupId', $groupId)
            ->executeStatement();
    }

    private function sourceTable(string $type): string
    {
        return 'tl_' . $type;
    }

    private function groupTable(string $type): string
    {
        return $this->sourceTable($type) . '_group';
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
    private function uniqueGroups(string $type, array $groups): array
    {
        $values = array_values(array_unique(array_map('intval', $groups)));

        return $this->connection->createQueryBuilder()
            ->select('id')
            ->from($this->groupTable($type))
            ->where('id IN (:groups)')
            ->setParameter('groups', $values, ArrayParameterType::INTEGER)
            ->execute()
            ->fetchFirstColumn();
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
