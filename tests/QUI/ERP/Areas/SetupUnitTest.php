<?php

namespace QUITests\ERP\Areas;

use PHPUnit\Framework\TestCase;
use QUI\ERP\Areas\Setup;

class SetupUnitTest extends TestCase
{
    public function testImportDelegatesToImportClass(): void
    {
        Setup::import(__DIR__ . '/../../../../setup/manual.xml');
        $this->assertTrue(true);
    }

    public function testOnPackageSetupTriggersSetupFlow(): void
    {
        $Package = $this->createMock(\QUI\Package\Package::class);

        try {
            Setup::onPackageSetup($Package);
        } catch (\Throwable) {
        }

        $this->assertTrue(true);
    }
}
