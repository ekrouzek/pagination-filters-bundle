<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\DatetimeDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class DatetimeDataFieldTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testEqAcceptsValidDatetimeAndStripsQuotes(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $expr = $field->eq($this->queryBuilder, '"2020-01-01 00:00:00"');

        self::assertSame("c.created = '2020-01-01 00:00:00'", (string) $expr);
    }

    public function testEqThrowsForInvalidDatetime(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(FilterParseException::class);

        $field->eq($this->queryBuilder, 'not-a-date');
    }

    public function testNeqThrowsForInvalidDatetime(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(FilterParseException::class);

        $field->neq($this->queryBuilder, 'not-a-date');
    }

    public function testGtAcceptsValidDatetime(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $expr = $field->gt($this->queryBuilder, '2020-01-01');

        self::assertSame("c.created > '2020-01-01'", (string) $expr);
    }

    public function testGtThrowsForInvalidDatetime(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(FilterParseException::class);

        $field->gt($this->queryBuilder, 'not-a-date');
    }

    public function testLteThrowsForInvalidDatetime(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(FilterParseException::class);

        $field->lte($this->queryBuilder, 'not-a-date');
    }

    public function testLikeIsUnsupportedForDatetimeFields(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->like($this->queryBuilder, '2020-01-01');
    }

    public function testNotLikeIsUnsupportedForDatetimeFields(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->notLike($this->queryBuilder, '2020-01-01');
    }

    public function testInAcceptsListOfValidDatetimesAndStripsQuotes(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $expr = $field->in($this->queryBuilder, '"2020-01-01","2020-02-01"');

        self::assertSame("c.created IN('2020-01-01', '2020-02-01')", (string) $expr);
    }

    public function testInThrowsForInvalidDatetimeInList(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $this->expectException(FilterParseException::class);

        $field->in($this->queryBuilder, '2020-01-01,not-a-date');
    }

    public function testNotInAcceptsListOfValidDatetimes(): void
    {
        $field = new DatetimeDataField('created', 'c.created');

        $expr = $field->notIn($this->queryBuilder, '2020-01-01,2020-02-01');

        self::assertSame("c.created NOT IN('2020-01-01', '2020-02-01')", (string) $expr);
    }
}
