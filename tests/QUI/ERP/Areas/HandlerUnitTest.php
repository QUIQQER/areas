<?php

namespace QUITests\ERP\Areas;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Area;
use QUI\ERP\Areas\Handler;

class HandlerUnitTest extends TestCase
{
    private \QUI\Database\DB|null $oldDatabase = null;
    private ?array $oldAvailableLanguages = null;

    protected function tearDown(): void
    {
        if ($this->oldDatabase !== null) {
            \QUI::$DataBase2 = $this->oldDatabase;
            $this->oldDatabase = null;
        }

        if ($this->oldAvailableLanguages !== null) {
            $this->setAvailableLanguagesCache($this->oldAvailableLanguages);
            $this->oldAvailableLanguages = null;
        }

        parent::tearDown();
    }

    public function testConstructorAndStaticGetters(): void
    {
        $Handler = new Handler();

        $this->assertSame('QUI\\ERP\\Areas\\Area', $Handler->getChildClass());
        $this->assertIsString($Handler->getDataBaseTableName());
        $this->assertNotSame('', $Handler->getDataBaseTableName());
    }

    public function testGetChildAttributes(): void
    {
        $Handler = $this->createHandlerWithAreas([]);

        $this->assertSame(['countries', 'data'], $Handler->getChildAttributes());
    }

    public function testSearchByTitle(): void
    {
        $AreaEurope = $this->createAreaMock('Europe', []);
        $AreaAmerica = $this->createAreaMock('America', []);

        $Handler = $this->createHandlerWithAreas([$AreaEurope, $AreaAmerica]);
        $result = $Handler->search('euro');

        $this->assertCount(1, $result);
        $this->assertSame($AreaEurope, $result[0]);
    }

    public function testSearchByCountryData(): void
    {
        $Country = new class {
            public function getName(): string
            {
                return 'Germany';
            }

            public function getCode(): string
            {
                return 'DE';
            }

            public function getCodeToLower(): string
            {
                return 'de';
            }

            public function getCurrencyCode(): string
            {
                return 'EUR';
            }
        };

        $Area = $this->createAreaMock('No title match', [$Country]);
        $Handler = $this->createHandlerWithAreas([$Area]);

        $result = $Handler->search('eur');

        $this->assertCount(1, $result);
        $this->assertSame($Area, $result[0]);
    }

    public function testSearchLimitWithStartAndMax(): void
    {
        $Area1 = $this->createAreaMock('One', []);
        $Area2 = $this->createAreaMock('Two', []);
        $Area3 = $this->createAreaMock('Three', []);

        $Handler = $this->createHandlerWithAreas([$Area1, $Area2, $Area3]);
        $result = $Handler->search('', ['limit' => '1,1']);

        $this->assertCount(1, $result);
        $this->assertSame($Area2, $result[0]);
    }

    public function testSearchLimitWithMaxOnly(): void
    {
        $Area1 = $this->createAreaMock('One', []);
        $Area2 = $this->createAreaMock('Two', []);
        $Area3 = $this->createAreaMock('Three', []);

        $Handler = $this->createHandlerWithAreas([$Area1, $Area2, $Area3]);
        $result = $Handler->search('', ['limit' => '2']);

        $this->assertCount(2, $result);
        $this->assertSame($Area1, $result[0]);
        $this->assertSame($Area2, $result[1]);
    }

    public function testGetChildTriggersUnderlyingLookup(): void
    {
        $Handler = new Handler();

        $this->mockDatabaseFetchResult([[
            'id' => 1,
            'countries' => 'DE',
            'data' => ''
        ]]);

        $Area = $Handler->getChild(1);
        $this->assertInstanceOf(Area::class, $Area);
    }

    public function testGetUnAssignedCountriesWithStubbedChildrenData(): void
    {
        $Handler = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildrenData'])
            ->getMock();

        $Handler->method('getChildrenData')->willReturn([
            ['countries' => 'DE,FR']
        ]);

        $result = $Handler->getUnAssignedCountries();

        $this->assertIsArray($result);
        $this->assertNotContains('DE', $result);
        $this->assertNotContains('FR', $result);
    }

    public function testGetLocaleDataWithEmptyDataReturnsEmptyArray(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute'])
            ->getMock();

        $Area->method('getAttribute')->with('data')->willReturn('');

        $Handler = new Handler();

        $this->assertSame([], $Handler->getLocaleData($Area));
    }

    public function testGetLocaleDataWithImportLocaleString(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'getTitle'])
            ->getMock();

        $Area->method('getAttribute')->willReturnCallback(static function (string $name) {
            if ($name === 'data') {
                return json_encode(['importLocale' => '[quiqqer/areas] package.title']);
            }

            return false;
        });

        $Area->method('getTitle')->willReturn('');

        $Handler = new Handler();
        $data = $Handler->getLocaleData($Area);

        $this->assertIsArray($data);
    }

    public function testGetLocaleDataWithTitleFallback(): void
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'getTitle'])
            ->getMock();

        $Area->method('getAttribute')->willReturnCallback(static function (string $name) {
            if ($name === 'data') {
                return json_encode([]);
            }

            if ($name === 'title') {
                return 'Plain title';
            }

            return false;
        });

        $Area->method('getTitle')->willReturn('');

        $Handler = new Handler();
        $data = $Handler->getLocaleData($Area);

        $this->assertIsArray($data);
    }

    public function testGetLocaleDataHandlesNullAvailableLanguages(): void
    {
        $this->rememberAvailableLanguages();
        $this->setAvailableLanguagesCache(null);

        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'getTitle'])
            ->getMock();

        $Area->method('getAttribute')->with('data')->willReturn(json_encode(['importLocale' => 'Title']));
        $Area->method('getTitle')->willReturn('');

        $Handler = new Handler();
        $data = $Handler->getLocaleData($Area);

        $this->assertIsArray($data);
    }

    public function testGetLocaleDataUsesPlainTitleWhenNoLocaleStringFound(): void
    {
        $this->rememberAvailableLanguages();
        $this->setAvailableLanguagesCache(['de']);

        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttribute', 'getTitle'])
            ->getMock();

        $Area->method('getAttribute')->willReturnCallback(static function (string $name) {
            if ($name === 'data') {
                return json_encode([]);
            }

            if ($name === 'title') {
                return 'Fallback title';
            }

            return false;
        });

        $Area->method('getTitle')->willReturn('');

        $Handler = new Handler();
        $data = $Handler->getLocaleData($Area);

        $this->assertArrayHasKey('de', $data);
        $this->assertNotSame('', $data['de']);
    }

    /**
     * @param array<int, object> $countries
     */
    private function createAreaMock(string $title, array $countries): Area
    {
        $Area = $this->getMockBuilder(Area::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTitle', 'getCountries'])
            ->getMock();

        $Area->method('getTitle')->willReturn($title);
        $Area->method('getCountries')->willReturn($countries);

        return $Area;
    }

    /**
     * @param list<Area> $areas
     */
    private function createHandlerWithAreas(array $areas): Handler
    {
        $Handler = $this->getMockBuilder(Handler::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getChildren'])
            ->getMock();

        $Handler->method('getChildren')->willReturn($areas);

        return $Handler;
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

    private function rememberAvailableLanguages(): void
    {
        $Reflection = new \ReflectionClass(\QUI\Translator::class);
        $property = $Reflection->getProperty('availableLanguages');
        $property->setAccessible(true);

        $value = $property->getValue();
        $this->oldAvailableLanguages = is_array($value) ? $value : [];
    }

    /**
     * @param ?array<int, string> $languages
     */
    private function setAvailableLanguagesCache(?array $languages): void
    {
        \QUI\Cache\Manager::set('quiqqer/translator/availableLanguages', $languages);

        $Reflection = new \ReflectionClass(\QUI\Translator::class);
        $property = $Reflection->getProperty('availableLanguages');
        $property->setAccessible(true);
        $property->setValue($languages);
    }
}
