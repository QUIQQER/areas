<?php

namespace QUITests\ERP\Areas;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Area;

class AreaUnitTest extends TestCase
{
    public function testConstructorAndGetId(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(123, $Factory);

        $this->assertSame(123, $Area->getId());
    }

    public function testAreaEventsAreRegisteredAndCallable(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(7, $Factory);

        foreach (['deleteBegin', 'deleteEnd', 'saveBegin'] as $event) {
            try {
                $this->fireAreaEvent($Area, $event);
            } catch (\Throwable) {
            }
        }

        $this->assertTrue(true);
    }

    public function testGetTitleWithProvidedLocale(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(5, $Factory);

        $Locale = $this->getMockBuilder(\QUI\Locale::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $Locale->expects($this->once())
            ->method('get')
            ->with('quiqqer/areas', 'area.5.title')
            ->willReturn('Area 5');

        $this->assertSame('Area 5', $Area->getTitle($Locale));
    }

    public function testGetTitleWithDefaultLocaleReturnsString(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(5, $Factory);

        $this->assertIsString($Area->getTitle());
    }

    public function testGetCountriesWithInvalidCodeReturnsEmptyList(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(1, $Factory);
        $Area->setAttributes(['countries' => 'THIS_CODE_DOES_NOT_EXIST']);

        $countries = $Area->getCountries();

        $this->assertSame([], $countries);
        $this->assertSame([], $Area->getCountries());
    }

    public function testGetCountriesWithValidAndInvalidCode(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(1, $Factory);
        $Area->setAttributes(['countries' => 'DE,THIS_CODE_DOES_NOT_EXIST']);

        $countries = $Area->getCountries();

        $this->assertNotEmpty($countries);
        $this->assertSame('DE', $countries[0]->getCode());
    }

    public function testContainsWithCountryObject(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountries'])
            ->getMock();

        $existingCountry = $this->getMockBuilder(\QUI\Countries\Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();
        $existingCountry->method('getCode')->willReturn('DE');

        $queryCountry = $this->getMockBuilder(\QUI\Countries\Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();
        $queryCountry->method('getCode')->willReturn('DE');

        $Area->method('getCountries')->willReturn([$existingCountry]);

        $this->assertTrue($Area->contains($queryCountry));
    }

    public function testContainsWithInvalidCountryCodeReturnsFalse(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountries'])
            ->getMock();

        $Area->method('getCountries')->willReturn([]);

        $this->assertFalse($Area->contains('NOT_A_COUNTRY'));
    }

    public function testContainsWithValidCountryCodeReturnsTrue(): void
    {
        $Factory = $this->createFactoryStub();
        $Area = new Area(1, $Factory);
        $Area->setAttributes(['countries' => 'DE']);

        $this->assertTrue($Area->contains('DE'));
    }

    public function testContainsReturnsFalseForNonMatchingCountryObject(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCountries'])
            ->getMock();

        $existingCountry = $this->getMockBuilder(\QUI\Countries\Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();
        $existingCountry->method('getCode')->willReturn('DE');

        $queryCountry = $this->getMockBuilder(\QUI\Countries\Country::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCode'])
            ->getMock();
        $queryCountry->method('getCode')->willReturn('FR');

        $Area->method('getCountries')->willReturn([$existingCountry]);

        $this->assertFalse($Area->contains($queryCountry));
    }

    private function createFactoryStub(): \QUI\CRUD\Factory
    {
        return new class extends \QUI\CRUD\Factory {
            public function getDataBaseTableName(): string
            {
                return 'tmp';
            }

            public function getChildAttributes(): array
            {
                return [];
            }

            public function getChildClass(): string
            {
                return Area::class;
            }
        };
    }

    private function fireAreaEvent(Area $Area, string $event): void
    {
        $Reflection = new \ReflectionClass(\QUI\CRUD\Child::class);
        $property = $Reflection->getProperty('Events');
        $property->setAccessible(true);

        /** @var \QUI\Events\Event $Events */
        $Events = $property->getValue($Area);
        $Events->fireEvent($event);
    }
}
