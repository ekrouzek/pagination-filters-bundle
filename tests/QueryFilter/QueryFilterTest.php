<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\SortParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\QueryFilter;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\ArrayParamFetcher;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class QueryFilterTest extends TestCase
{
    private function createFilteredQueryFilter(): QueryFilter
    {
        return (new QueryFilter())
            ->addNumberField('id', 'c.id')
            ->addTextField('name', 'c.name')
            ->addDatetimeField('created', 'c.created')
            ->addBooleanField('active', 'c.active')
            ->addUuidField('uuid', 'c.uuid');
    }

    private function createQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return EntityManagerFactory::create()
            ->createQueryBuilder()
            ->select('c')
            ->from(Course::class, 'c');
    }

    public function testSimpleFilterExpressionIsAppliedToQuery(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:id:1']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.id = '1'",
            $result->getDQL()
        );
    }

    public function testAndOrExpressionsWithBracketsRespectPrecedence(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher([
            'filter' => '(eq:id:1 & like:name:"test") | eq:active:1',
        ]);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c '
            . "WHERE c.active = '1' OR (LOWER(c.name) LIKE '%test%' AND c.id = '1')",
            $result->getDQL()
        );
    }

    public function testUuidFilterExpressionIsAppliedToQuery(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:uuid:123e4567-e89b-12d3-a456-426614174000']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c "
            . "WHERE c.uuid = '123e4567-e89b-12d3-a456-426614174000'",
            $result->getDQL()
        );
    }

    public function testUuidFilterWithInvalidValueThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:uuid:not-a-uuid']);

        $this->expectException(FilterParseException::class);

        $queryFilter->filter($queryBuilder, $paramFetcher);
    }

    public function testFilterWithUnknownFieldKeyThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:unknown:1']);

        $this->expectException(FilterParseException::class);

        $queryFilter->filter($queryBuilder, $paramFetcher);
    }

    public function testSortAppliesOrderByForRequestedField(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => 'created:desc']);

        $result = $queryFilter->sort($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c ORDER BY c.created desc',
            $result->getDQL()
        );
    }

    public function testSortWithInvalidDirectionThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => 'created:sideways']);

        $this->expectException(SortParseException::class);

        $queryFilter->sort($queryBuilder, $paramFetcher);
    }
}
