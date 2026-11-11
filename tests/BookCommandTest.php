<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class BookCommandTest extends KernelTestCase
{
    public function testItPrintsTheBook(): void
    {
        $kernel = self::bootKernel();
        new MockIsbnLab(static::getContainer(), MockIsbnLab::json('book.json'));

        $tester = new CommandTester((new Application($kernel))->find('isbnlab:book'));
        $tester->execute(['isbn' => '9780765302304']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('The Gathering Storm', $tester->getDisplay());
        self::assertStringContainsString('Wheel of time #12', $tester->getDisplay());
    }

    public function testItExplainsAnError(): void
    {
        $kernel = self::bootKernel();
        new MockIsbnLab(static::getContainer(), MockIsbnLab::error(401, 'This endpoint needs an API token.'));

        $tester = new CommandTester((new Application($kernel))->find('isbnlab:book'));

        self::assertSame(1, $tester->execute(['isbn' => '9780765302304']));
        self::assertStringContainsString('ISBNLAB_API_TOKEN', $tester->getDisplay());
    }
}
