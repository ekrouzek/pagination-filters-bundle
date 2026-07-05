<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\BooleanDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class BooleanDataFieldTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testEqStripsSurroundingQuotes(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $expr = $field->eq($this->queryBuilder, '"1"');

        self::assertSame("c.active = '1'", (string) $expr);
    }

    public function testNeqAcceptsUnquotedValue(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $expr = $field->neq($this->queryBuilder, '0');

        self::assertSame("c.active <> '0'", (string) $expr);
    }

    public function testLikeIsUnsupportedForBooleanFields(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->like($this->queryBuilder, '1');
    }

    public function testLtIsUnsupportedForBooleanFields(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->lt($this->queryBuilder, '1');
    }

    public function testGteIsUnsupportedForBooleanFields(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->gte($this->queryBuilder, '1');
    }

    public function testInStripsSurroundingQuotesFromEachValue(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $expr = $field->in($this->queryBuilder, '"1","0"');

        self::assertSame("c.active IN('1', '0')", (string) $expr);
    }

    public function testNotInStripsSurroundingQuotesFromEachValue(): void
    {
        $field = new BooleanDataField('active', 'c.active');

        $expr = $field->notIn($this->queryBuilder, '1,0');

        self::assertSame("c.active NOT IN('1', '0')", (string) $expr);
    }
}
