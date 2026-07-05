<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\NumberDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class NumberDataFieldTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testEqAcceptsNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $expr = $field->eq($this->queryBuilder, '42');

        self::assertSame("c.id = '42'", (string) $expr);
    }

    public function testEqThrowsForNonNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->eq($this->queryBuilder, 'abc');
    }

    public function testNeqThrowsForNonNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->neq($this->queryBuilder, 'abc');
    }

    public function testGtAcceptsNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $expr = $field->gt($this->queryBuilder, '10');

        self::assertSame("c.id > '10'", (string) $expr);
    }

    public function testGtThrowsForNonNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->gt($this->queryBuilder, 'abc');
    }

    public function testLteAcceptsNumericValue(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $expr = $field->lte($this->queryBuilder, '10');

        self::assertSame("c.id <= '10'", (string) $expr);
    }

    public function testLikeIsUnsupportedForNumberFields(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->like($this->queryBuilder, '1');
    }

    public function testNotLikeIsUnsupportedForNumberFields(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->notLike($this->queryBuilder, '1');
    }

    public function testInAcceptsListOfNumericValues(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $expr = $field->in($this->queryBuilder, '1,2,3');

        self::assertSame("c.id IN('1', '2', '3')", (string) $expr);
    }

    public function testInThrowsForNonNumericValueInList(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->in($this->queryBuilder, '1,abc,3');
    }

    public function testNotInAcceptsListOfNumericValues(): void
    {
        $field = new NumberDataField('id', 'c.id');

        $expr = $field->notIn($this->queryBuilder, '1,2');

        self::assertSame("c.id NOT IN('1', '2')", (string) $expr);
    }
}
