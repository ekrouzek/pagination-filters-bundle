<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\TextDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class TextDataFieldTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testEqStripsSurroundingQuotes(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->eq($this->queryBuilder, '"test value"');

        self::assertSame("c.name = 'test value'", (string) $expr);
    }

    public function testLikeStripsSurroundingQuotesAndLowercases(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->like($this->queryBuilder, '"Test"');

        self::assertSame("LOWER(c.name) LIKE '%test%'", (string) $expr);
    }

    public function testLtIsUnsupportedForTextFields(): void
    {
        $field = new TextDataField('name', 'c.name');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->lt($this->queryBuilder, '1');
    }

    public function testGteIsUnsupportedForTextFields(): void
    {
        $field = new TextDataField('name', 'c.name');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->gte($this->queryBuilder, '1');
    }

    public function testInStripsSurroundingQuotesFromEachValue(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->in($this->queryBuilder, '"foo","bar",baz');

        self::assertSame("c.name IN('foo', 'bar', 'baz')", (string) $expr);
    }

    public function testNotInStripsSurroundingQuotesFromEachValue(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->notIn($this->queryBuilder, '"foo","bar"');

        self::assertSame("c.name NOT IN('foo', 'bar')", (string) $expr);
    }

    public function testInRespectsCommasInsideQuotedValues(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->in($this->queryBuilder, '"foo, bar","baz"');

        self::assertSame("c.name IN('foo, bar', 'baz')", (string) $expr);
    }

    public function testInThrowsForEmptyValueList(): void
    {
        $field = new TextDataField('name', 'c.name');

        $this->expectException(FilterParseException::class);

        $field->in($this->queryBuilder, '');
    }
}
