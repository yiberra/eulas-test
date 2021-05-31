<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ImportProductsCommandTest extends KernelTestCase
{

    public function testImport(): void
    {

        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('import:products');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('[OK]', $output);

    }

}
