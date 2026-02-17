<?php

namespace QUITests\ERP\Areas;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Area;
use QUI\ERP\Areas\Utils;
use QUI\Interfaces\Users\User;

class UtilsUnitTest extends TestCase
{
    private \QUI\Database\DB|null $oldDatabase = null;

    protected function tearDown(): void
    {
        if ($this->oldDatabase !== null) {
            \QUI::$DataBase2 = $this->oldDatabase;
            $this->oldDatabase = null;
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
        $this->mockDatabaseToThrowOnFetch();

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
        $this->mockDatabaseToThrowOnFetch();

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
        $this->mockDatabaseToThrowOnFetch();

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

    private function mockDatabaseToThrowOnFetch(): void
    {
        $this->oldDatabase = \QUI::$DataBase2;

        $DB = $this->getMockBuilder(\QUI\Database\DB::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch'])
            ->getMock();

        $DB->method('fetch')->willThrowException(new \QUI\Exception('db fail'));
        \QUI::$DataBase2 = $DB;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function mockDatabaseFetchResult(array $rows): void
    {
        $this->oldDatabase = \QUI::$DataBase2;

        $DB = $this->getMockBuilder(\QUI\Database\DB::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetch'])
            ->getMock();

        $DB->method('fetch')->willReturn($rows);
        \QUI::$DataBase2 = $DB;
    }
}
