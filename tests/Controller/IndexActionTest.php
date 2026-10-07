<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Controller\IndexAction;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class IndexActionTest extends TestCase
{
    /** @return iterable<string, array{array<string, string>, string, array<string, int|null>}> */
    public static function queries(): iterable
    {
        yield 'no params: all rows by id' => [[], 'ORDER BY id ASC', []];
        yield 'other action ignores sort and i' => [['akcja' => '1', 'sort' => '1', 'i' => '3'], 'ORDER BY id ASC', []];
        yield 'action 5 sort 1' => [
            ['akcja' => '5', 'sort' => '1', 'i' => '2'],
            'WHERE (kwota > 10) AND (id = :id) ORDER BY 2, 4 ASC',
            ['id' => 2],
        ];
        yield 'action 5 sort 2' => [
            ['akcja' => '5', 'sort' => '2', 'i' => '2'],
            'WHERE (kwota > 10) AND (id = :id) ORDER BY 10 DESC',
            ['id' => 2],
        ];
        yield 'action 5 without sort' => [['akcja' => '5', 'i' => 'abc'], 'WHERE (kwota > 10) AND (id = :id)', ['id' => 0]];
    }

    /**
     * @param array<string, string> $query
     * @param array<string, int|null> $expectedParams
     */
    #[DataProvider('queries')]
    public function testBuildsQueryFromUrlParameters(array $query, string $expectedSql, array $expectedParams): void
    {
        $this->invoke($query, [], $sql, $params);

        self::assertStringContainsString($expectedSql, $sql);
        self::assertSame($expectedParams, $params);
    }

    public function testRendersIdAndNameOnly(): void
    {
        $response = $this->invoke([], [self::row(1, 'ABC Sp. z o.o.', '5.50'), self::row(2, 'XYZ S.A.', '7.75')]);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
        $html = (string) $response->getBody();
        self::assertStringContainsString('<td>ABC Sp. z o.o.</td>', $html);
        self::assertStringNotContainsString('5.50', $html);
    }

    public function testAction5AppendsAmountWhenAboveFive(): void
    {
        $html = (string) $this->invoke(
            ['akcja' => '5', 'i' => '4'],
            [self::row(4, 'GHI Trading', '15.99'), self::row(5, 'JKL Consulting', '4.50')],
        )->getBody();

        self::assertStringContainsString('<td>GHI Trading 15.99</td>', $html);
        self::assertStringContainsString('<td>JKL Consulting</td>', $html);
    }

    public function testAction5KeepsTwoDecimalsForFloatAmounts(): void
    {
        // SQLite returns DECIMAL columns as float, so 6.80 arrives as 6.8.
        $html = (string) $this->invoke(['akcja' => '5', 'i' => '6'], [self::row(6, 'MNO Services', 6.8)])->getBody();

        self::assertStringContainsString('<td>MNO Services 6.80</td>', $html);
    }

    public function testNameIsEscaped(): void
    {
        $html = (string) $this->invoke([], [self::row(1, '<script>x</script>', '1.00')])->getBody();

        self::assertStringNotContainsString('<script>x', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
    }

    /** @return list<mixed> a `contracts` row as returned by fetchAllNumeric() */
    private static function row(int $id, string $name, string|float $amount): array
    {
        return [$id, 'REF', $name, 'Typ', '1234567890', 'Aktywny', 'Miasto', '2024-01-01', 'Osoba', 'Status', $amount];
    }

    /**
     * @param array<string, string> $query
     * @param list<list<mixed>> $rows
     */
    private function invoke(array $query, array $rows, ?string &$sql = null, ?array &$params = null): \Psr\Http\Message\ResponseInterface
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchAllNumeric')->willReturn($rows);

        $connection = $this->createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new SQLitePlatform());
        $connection->method('createQueryBuilder')->willReturnCallback(static fn (): QueryBuilder => new QueryBuilder($connection));
        $connection->method('executeQuery')->willReturnCallback(
            static function (string $executedSql, array $executedParams = []) use ($result, &$sql, &$params): Result {
                $sql = $executedSql;
                $params = $executedParams;

                return $result;
            },
        );

        $factory = new Psr17Factory();
        $request = (new ServerRequest('GET', '/'))->withQueryParams($query);
        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../templates'));

        return (new IndexAction())($request, $factory, $connection, $twig);
    }
}
