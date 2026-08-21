<?php

declare(strict_types=1);

namespace QUITests\ERP\Areas;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;

abstract class DatabaseTestCase extends TestCase
{
    private ?Connection $oldConnection = null;
    private ?Connection $testConnection = null;
    private bool $ciTransactionActive = false;

    protected function tearDown(): void
    {
        if ($this->ciTransactionActive && $this->testConnection?->isTransactionActive()) {
            $this->testConnection->rollBack();
        }

        $this->ciTransactionActive = false;

        if ($this->oldConnection !== null) {
            $this->setQueryBuilderConnection($this->oldConnection);
            $this->oldConnection = null;
        }

        if (!DatabaseEnvironment::usesCiDatabase()) {
            $this->testConnection?->close();
        }

        $this->testConnection = null;

        parent::tearDown();
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    protected function useAreaFixtures(array $rows): void
    {
        if ($this->oldConnection === null) {
            $this->oldConnection = \QUI::getDataBaseConnection();
        }

        if (DatabaseEnvironment::usesCiDatabase()) {
            $Connection = $this->oldConnection;
            $Connection->beginTransaction();
            $this->ciTransactionActive = true;
            $Connection->delete(\QUI::getDBTableName('areas'), []);
        } else {
            $Connection = DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'memory' => true
            ]);

            $schema = new Schema();
            $Table = $schema->createTable(\QUI::getDBTableName('areas'));
            $Table->addColumn('id', 'integer');
            $Table->addColumn('countries', 'text', ['notnull' => false]);
            $Table->addColumn('data', 'text', ['notnull' => false]);
            $Table->setPrimaryKey(['id']);

            foreach ($schema->toSql($Connection->getDatabasePlatform()) as $statement) {
                $Connection->executeStatement($statement);
            }
        }

        foreach ($rows as $row) {
            $Connection->insert(\QUI::getDBTableName('areas'), $row);
        }

        $this->testConnection = $Connection;
        $this->setQueryBuilderConnection($Connection);
    }

    private function setQueryBuilderConnection(Connection $Connection): void
    {
        $Reflection = new \ReflectionClass(\QUI::class);
        $property = $Reflection->getProperty('QueryBuilder');
        $property->setAccessible(true);
        $property->setValue($Connection);
    }
}
