<?php

namespace QUITests\ERP\Areas;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Area;
use QUI\ERP\Areas\Utils;
use QUI\Interfaces\Users\User;

class UtilsUnitTest extends TestCase
{
    private ?Connection $oldConnection = null;

    protected function tearDown(): void
    {
        if ($this->oldConnection !== null) {
            $this->setQueryBuilderConnection($this->oldConnection);
            $this->oldConnection = null;
        }

        parent::tearDown();
    }

    public function testIsUserInAreasReturnsTrueForMatchingArea(): void
    {
        $Country = $this->createCountryCodeMock('DE');
        $User = $this->createMock(User::class);
        $User->method('getCountry')->willReturn($Country);

        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['contains'])
            ->getMock();

        $Area->method('contains')->with($Country)->willReturn(true);

        $this->assertTrue(Utils::isUserInAreas($User, [$Area]));
    }

    public function testIsUserInAreasWithInvalidAreaIdFallsBackToFalse(): void
    {
        $this->mockDatabaseFetchResult([]);

        $Country = $this->createCountryCodeMock('DE');
        $User = $this->createMock(User::class);
        $User->method('getCountry')->willReturn($Country);

        $this->assertFalse(Utils::isUserInAreas($User, [999999]));
    }

    public function testIsAddressInAreaReturnsTrueForMatchingArea(): void
    {
        $Country = $this->createCountryCodeMock('DE');

        $Address = $this->getMockBuilder(\QUI\Users\Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $Address->method('getCountry')->willReturn($Country);

        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['contains'])
            ->getMock();

        $Area->method('contains')->with($Country)->willReturn(true);

        $this->assertTrue(Utils::isAddressInArea($Address, [$Area]));
    }

    public function testIsAddressInAreaReturnsFalseOnCountryException(): void
    {
        $Address = $this->getMockBuilder(\QUI\Users\Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $Address->method('getCountry')->willThrowException(new \Exception('no country'));

        $this->assertFalse(Utils::isAddressInArea($Address, []));
    }

    public function testIsAddressInAreaWithInvalidAreaIdFallsBackToFalse(): void
    {
        $this->mockDatabaseFetchResult([]);

        $Country = $this->createCountryCodeMock('DE');

        $Address = $this->getMockBuilder(\QUI\Users\Address::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountry'])
            ->getMock();

        $Address->method('getCountry')->willReturn($Country);

        $this->assertFalse(Utils::isAddressInArea($Address, [999999]));
    }

    public function testGetAreaByCountryReturnsNullForInvalidCountry(): void
    {
        $this->assertNull(Utils::getAreaByCountry(new \stdClass()));
    }

    public function testGetAreaByCountryReturnsNullOnHandlerException(): void
    {
        $this->mockDatabaseFetchResult([]);

        $Country = \QUI\Countries\Manager::get('DE');
        $this->assertNull(Utils::getAreaByCountry($Country));
    }

    public function testGetAreaByCountryReturnsAreaForMatchingCountry(): void
    {
        $this->mockDatabaseFetchResult([[
            'id' => 10,
            'countries' => 'DE',
            'data' => ''
        ]]);

        $Country = \QUI\Countries\Manager::get('DE');
        $Area = Utils::getAreaByCountry($Country);

        $this->assertInstanceOf(Area::class, $Area);
    }

    public function testGetAreaByCountryReturnsNullWhenNoAreaContainsCountry(): void
    {
        $this->mockDatabaseFetchResult([[
            'id' => 11,
            'countries' => 'FR',
            'data' => ''
        ]]);

        $Country = \QUI\Countries\Manager::get('DE');
        $this->assertNull(Utils::getAreaByCountry($Country));
    }

    private function createCountryCodeMock(string $code): \QUI\Countries\Country
    {
        $Country = $this->getMockBuilder(\QUI\Countries\Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();

        $Country->method('getCode')->willReturn($code);

        return $Country;
    }

    private function mockDatabaseFetchResult(array $rows): void
    {
        if ($this->oldConnection === null) {
            $this->oldConnection = \QUI::getDataBaseConnection();
        }

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

        foreach ($rows as $row) {
            $Connection->insert(\QUI::getDBTableName('areas'), $row);
        }

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
