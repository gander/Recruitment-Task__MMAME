<?php

declare(strict_types=1);

namespace App\Controller;

use Doctrine\DBAL\Connection as DbalConnection;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Routing\Attribute\Route;
use Twig\Environment as Templating;

#[Route('/')]
final class IndexAction
{
    public function __invoke(
        ServerRequestInterface   $request,
        ResponseFactoryInterface $responseFactory,
        DbalConnection           $connection,
        Templating               $twig,
    ): ResponseInterface {
        // Get query parameters and apply type casting
        $params = $request->getQueryParams();
        $action = isset($params['akcja']) ? (int)$params['akcja'] : null;
        $sort = isset($params['sort']) ? (int)$params['sort'] : null;
        $id = isset($params['i']) ? (int)$params['i'] : null;

        // Build the query
        $query = $this->buildQuery($connection, $action, $sort, $id);

        // Execute the query
        $records = $query->executeQuery()->fetchAllNumeric();

        // Process the records
        $records = $this->processRecords($records, $action);

        // Render the response
        return $this->renderResponse($responseFactory, $twig, $records);
    }

    /**
     * Builds SQL query based on provided parameters
     */
    private function buildQuery(DbalConnection $connection, ?int $action, ?int $sort, ?int $id): \Doctrine\DBAL\Query\QueryBuilder
    {
        $query = $connection->createQueryBuilder()
            ->select('*')
            ->from('contracts');

        if ($action === 5) {
            $query
                ->where('kwota > 10')
                ->andWhere('id = :id')
                ->setParameter('id', $id);

            match ($sort) {
                1 => $query->addOrderBy('2, 4', 'ASC'),
                2 => $query->addOrderBy('10', 'DESC'),
                default => null,
            };
        } else {
            $query->orderBy('id', 'ASC');
        }

        return $query;
    }

    /**
     * Processes query results before passing them to the view
     */
    private function processRecords(array $records, ?int $action): array
    {
        // Modify records for action 5
        if ($action === 5) {
            $records = array_map(static function (array $record) {
                if ($record[10] > 5) {
                    $record[2] = sprintf('%s %s', $record[2], number_format((float) $record[10], 2, '.', ''));
                }
                return $record;
            }, $records);
        }

        // Limit data passed to the template for security
        return array_map(static fn (array $record) => [$record[0], $record[2]], $records);
    }

    /**
     * Creates HTTP response with rendered template
     */
    private function renderResponse(
        ResponseFactoryInterface $responseFactory,
        Templating $twig,
        array $records
    ): ResponseInterface {
        $html = $twig->render('index.html.twig', ['records' => $records]);

        return $responseFactory
            ->createResponse()
            ->withHeader('Content-Type', 'text/html')
            ->withBody($responseFactory->createStream($html));
    }
}
