<?php

namespace QUITests\ERP\Areas;

use QUI\ERP\Areas\Area;
use QUI\ERP\Areas\Utils;
use QUI\Interfaces\Users\User;
use QUITests\ERP\Areas\DatabaseTestCase;

class UtilsUnitTest extends DatabaseTestCase
{
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

    public function testGetAreaByCountryReturnsNullWhenNoAreasExist(): void
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
        $this->useAreaFixtures($rows);
    }
}
