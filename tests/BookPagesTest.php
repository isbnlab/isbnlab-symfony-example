<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BookPagesTest extends WebTestCase
{
    public function testTheFormGoesToTheBookPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/?isbn=978-0-7653-0230-4');

        self::assertResponseRedirects('/book/9780765302304');
    }

    public function testABookPageShowsTheBook(): void
    {
        $client = static::createClient();
        $api = new MockIsbnLab(static::getContainer(), MockIsbnLab::json('book.json'));

        $client->request('GET', '/book/9780765302304');

        self::assertResponseIsSuccessful();
        self::assertSame('/api/book/isbn/9780765302304', $api->lastPath());
        self::assertSame('Bearer test-token', $api->requests[0]['request']->getHeaderLine('Authorization'));
        self::assertSelectorTextContains('h1', 'The Gathering Storm');
        self::assertSelectorTextContains('dl', 'Robert Jordan');
        self::assertSelectorExists('dl a[href="/author/3705408"]');
        self::assertSelectorTextContains('dl', 'Wheel of time, book 12');
        self::assertSelectorExists('table tbody tr');
    }

    public function testABookThatIsNotInTheCatalogIsA404(): void
    {
        $client = static::createClient();
        new MockIsbnLab(static::getContainer(), MockIsbnLab::error(404, 'Not Found'));

        $client->request('GET', '/book/9780000000000');

        self::assertResponseStatusCodeSame(404);
        self::assertSelectorTextContains('.error', 'not in the ISBNLab catalog');
    }

    public function testAPlanErrorShowsTheApisExplanation(): void
    {
        $client = static::createClient();
        new MockIsbnLab(static::getContainer(), MockIsbnLab::error(403, 'This endpoint needs a Standard or Pro plan. See isbnlab.com/pricing.'));

        $client->request('GET', '/author/3705408');

        self::assertResponseStatusCodeSame(403);
        self::assertSelectorTextContains('.error', 'needs a Standard or Pro plan');
    }

    public function testAnAuthorPage(): void
    {
        $client = static::createClient();
        $api = new MockIsbnLab(static::getContainer(), MockIsbnLab::json('author.json'));

        $client->request('GET', '/author/3705408');

        self::assertResponseIsSuccessful();
        self::assertSame('/api/author/3705408', $api->lastPath());
        self::assertSelectorTextContains('h1', 'Robert Jordan');
    }

    public function testTheIsbnPage(): void
    {
        $client = static::createClient();
        $api = new MockIsbnLab(static::getContainer(), MockIsbnLab::json('isbn-details.json'));

        $client->request('GET', '/isbn/0765302306');

        self::assertResponseIsSuccessful();
        self::assertSame('/api/isbn/isbn-lookup/0765302306', $api->lastPath());
        self::assertSelectorTextContains('dl', '978-0-7653-0230-4');
    }
}
