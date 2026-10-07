<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class IndexTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();

        // The in-memory database lives as long as this kernel's connection: migrate it with the real migrations.
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput(['command' => 'doctrine:migrations:migrate', '--no-interaction' => true]), new NullOutput());
    }

    public function testListsAllThirtyContractsById(): void
    {
        $rows = $this->rows('/');

        self::assertResponseStatusCodeSame(200);
        self::assertCount(30, $rows);
        self::assertSame(range(1, 30), array_map('intval', array_column($rows, 0)));
        self::assertSame(['ABC Sp. z o.o.', 'XYZ S.A.'], \array_slice(array_column($rows, 1), 0, 2));
    }

    public function testAction5ListsTheRequestedContractWithItsAmount(): void
    {
        $rows = $this->rows('/?akcja=5&sort=1&i=4');

        self::assertResponseStatusCodeSame(200);
        self::assertSame([['4', 'GHI Trading 15.99']], $rows);
    }

    public function testAction5SortedByAmountKeepsTwoDecimals(): void
    {
        $rows = $this->rows('/?akcja=5&sort=2&i=10');

        self::assertSame([['10', 'YZA Industries 18.40']], $rows);
    }

    public function testAction5IgnoresContractsWithAmountUpToTen(): void
    {
        self::assertSame([], $this->rows('/?akcja=5&sort=1&i=1'));
        self::assertResponseStatusCodeSame(200);
    }

    public function testOtherActionIgnoresFilters(): void
    {
        self::assertCount(30, $this->rows('/?akcja=1&sort=2&i=4'));
    }

    /** @return list<list<string>> the [id, name] cells of every table row */
    private function rows(string $uri): array
    {
        $crawler = $this->client->request('GET', $uri);

        return $crawler->filterXPath('//table//tr')->each(
            static fn ($row): array => $row->filterXPath('.//td')->each(static fn ($cell): string => trim($cell->text())),
        );
    }
}
