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

    public function testInFilterExpressionIsAppliedToQuery(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'in:id:1,2,3']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.id IN('1', '2', '3')",
            $result->getDQL()
        );
    }

    public function testNotInFilterExpressionIsAppliedToQuery(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'not-in:name:"foo","bar"']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c "
            . "WHERE c.name NOT IN('foo', 'bar')",
            $result->getDQL()
        );
    }

    public function testInFilterWithNonNumericValueThrowsForNumberField(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'in:id:1,not-a-number']);

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

    public function testSortWithUnknownFieldKeyThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => 'unknown:asc']);

        $this->expectException(SortParseException::class);

        $queryFilter->sort($queryBuilder, $paramFetcher);
    }

    public function testSortWithMalformedStringThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => 'created:asc:extra']);

        $this->expectException(SortParseException::class);

        $queryFilter->sort($queryBuilder, $paramFetcher);
    }

    public function testSortFallsBackToDefaultSortWhenNoSortParamGiven(): void
    {
        $queryFilter = $this->createFilteredQueryFilter()->addDefaultSort('created', 'desc');
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => '']);

        $result = $queryFilter->sort($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c ORDER BY c.created desc',
            $result->getDQL()
        );
    }

    public function testSortWithoutSortParamOrDefaultSortLeavesQueryUnchanged(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['sort' => '']);

        $result = $queryFilter->sort($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c',
            $result->getDQL()
        );
    }

    public function testAddDefaultSortWithUnknownFieldKeyThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();

        $this->expectException(SortParseException::class);

        $queryFilter->addDefaultSort('unknown');
    }

    public function testAddDefaultSortWithInvalidDirectionThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();

        $this->expectException(SortParseException::class);

        $queryFilter->addDefaultSort('created', 'sideways');
    }

    public function testFilterWithEmptyStringLeavesQueryUnchanged(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => '']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c',
            $result->getDQL()
        );
    }

    public function testFilterWithTooFewPartsThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:id']);

        $this->expectException(FilterParseException::class);

        $queryFilter->filter($queryBuilder, $paramFetcher);
    }

    public function testFilterWithUnsupportedOperatorThrows(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'unsupported:id:1']);

        $this->expectException(FilterParseException::class);

        $queryFilter->filter($queryBuilder, $paramFetcher);
    }

    public function testFilterWithExtraColonsInValueAreUnitedIntoThirdSlot(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'eq:created:2020-01-01 00:00:00']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.created = '2020-01-01 00:00:00'",
            $result->getDQL()
        );
    }

    public function testIsNullAndIsNotNullFilterExpressions(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'is-null:name:']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.name IS NULL',
            $result->getDQL()
        );
    }

    public function testIsMemberOfFilterExpression(): void
    {
        $queryFilter = $this->createFilteredQueryFilter()->addTextField('tags', 'c.tags');
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'is-member-of:tags:1']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            'SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE 1 MEMBER OF c.tags',
            $result->getDQL()
        );
    }

    public function testInAndNotInFilterExpressions(): void
    {
        $queryFilter = $this->createFilteredQueryFilter();
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher(['filter' => 'in:id:1,2,3']);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.id IN('1', '2', '3')",
            $result->getDQL()
        );
    }

    public function testUuidFieldFilterExpression(): void
    {
        $queryFilter = $this->createFilteredQueryFilter()->addUuidField('uuid', 'c.uuid');
        $queryBuilder = $this->createQueryBuilder();
        $paramFetcher = new ArrayParamFetcher([
            'filter' => 'eq:uuid:123e4567-e89b-12d3-a456-426614174000',
        ]);

        $result = $queryFilter->filter($queryBuilder, $paramFetcher);

        self::assertSame(
            "SELECT c FROM Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course c WHERE c.uuid = '123e4567-e89b-12d3-a456-426614174000'",
            $result->getDQL()
        );
    }
}
