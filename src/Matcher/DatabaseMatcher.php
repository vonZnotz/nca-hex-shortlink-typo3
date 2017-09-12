<?php

namespace Nca\Shortlink\Matcher;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

class DatabaseMatcher implements MatcherInterface
{
    const DEFAULT_TABLE_NAME = 'table';
    const DEFAULT_SOURCE_COLUMN_NAME = 'source';
    const DEFAULT_DESTINATION_COLUMN_NAME = 'destination';

    /** @var Connection */
    private $connection;

    /** @var string */
    protected $sourceColumnName;

    /** @var string */
    protected $destinationColumnName;

    /** @var string */
    protected $tableName;

    public function __construct(
        Connection $connection,
        string $tableName = self::DEFAULT_TABLE_NAME,
        string $sourceColumnName = self::DEFAULT_TABLE_NAME,
        string $destinationColumnName = self::DEFAULT_SOURCE_COLUMN_NAME
    ) {
        $this->connection = $connection;
        $this->tableName = $tableName;
        $this->sourceColumnName = $sourceColumnName;
        $this->destinationColumnName = $destinationColumnName;
    }

    public function match(string $source): ?string
    {
        $expr = $this->connection->getExpressionBuilder();
        $destination = $this->connection->createQueryBuilder()
            ->select($this->connection->quoteIdentifier($this->destinationColumnName))
            ->from($this->connection->quoteIdentifier($this->tableName))
            ->where($expr->eq($this->connection->quoteIdentifier($this->sourceColumnName), ':search'))
            ->setMaxResults(1)
            ->setParameter('search', $source, Type::STRING)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        if ($destination === false) {
            return null;
        }

        return $destination;
    }

}
