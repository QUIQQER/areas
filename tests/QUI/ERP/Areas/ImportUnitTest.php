<?php

namespace QUITests\ERP\Areas;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Import;

class ImportUnitTest extends TestCase
{
    public function testGetAvailableImportsReturnsTypedEntries(): void
    {
        $imports = Import::getAvailableImports();

        $this->assertNotEmpty($imports);
        $this->assertIsArray($imports[0]);
        $this->assertArrayHasKey('file', $imports[0]);
        $this->assertArrayHasKey('locale', $imports[0]);
        $this->assertIsString($imports[0]['file']);
        $this->assertIsArray($imports[0]['locale']);
        $this->assertCount(2, $imports[0]['locale']);

        $importsByFile = array_column($imports, null, 'file');

        $this->assertSame(
            ['quiqqer/areas', 'area.import.manual'],
            $importsByFile['manual.xml']['locale']
        );
    }

    public function testExistPreconfigureRecognizesKnownFile(): void
    {
        $imports = Import::getAvailableImports();

        $this->assertTrue(Import::existPreconfigure($imports[0]['file']));
        $this->assertFalse(Import::existPreconfigure('does-not-exist.xml'));
    }

    public function testImportPreconfigureAreasThrowsForUnknownFile(): void
    {
        $this->expectException(\QUI\Exception::class);

        Import::importPreconfigureAreas('does-not-exist.xml');
    }

    public function testImportPreconfigureAreasWithExistingFile(): void
    {
        $imports = Import::getAvailableImports();
        Import::importPreconfigureAreas($imports[0]['file']);

        $this->assertTrue(true);
    }

    public function testImportProcessesXmlAndDoesNotThrow(): void
    {
        Import::import(__DIR__ . '/../../../../setup/manual.xml');
        $this->assertTrue(true);
    }
}
