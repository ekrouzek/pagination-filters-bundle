<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\UuidDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class UuidDataFieldTest extends TestCase
{
    private const VALID_UUID = '123e4567-e89b-12d3-a456-426614174000';
    private const VALID_UUID_2 = '223e4567-e89b-12d3-a456-426614174001';

    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testEqStripsSurroundingQuotes(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $expr = $field->eq($this->queryBuilder, '"' . self::VALID_UUID . '"');

        self::assertSame("c.id = '" . self::VALID_UUID . "'", (string) $expr);
    }

    public function testNeqAcceptsUnquotedUuid(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $expr = $field->neq($this->queryBuilder, self::VALID_UUID);

        self::assertSame("c.id <> '" . self::VALID_UUID . "'", (string) $expr);
    }

    public function testEqThrowsForInvalidUuid(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->eq($this->queryBuilder, 'not-a-uuid');
    }

    public function testNeqThrowsForInvalidUuid(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->neq($this->queryBuilder, 'not-a-uuid');
    }

    public function testLikeIsUnsupportedForUuidFields(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->like($this->queryBuilder, self::VALID_UUID);
    }

    public function testLtIsUnsupportedForUuidFields(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->lt($this->queryBuilder, self::VALID_UUID);
    }

    public function testGteIsUnsupportedForUuidFields(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(UnsupportedDataFieldMethodException::class);

        $field->gte($this->queryBuilder, self::VALID_UUID);
    }

    public function testInAcceptsListOfValidUuids(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $expr = $field->in($this->queryBuilder, self::VALID_UUID . ',"' . self::VALID_UUID_2 . '"');

        self::assertSame(
            "c.id IN('" . self::VALID_UUID . "', '" . self::VALID_UUID_2 . "')",
            (string) $expr
        );
    }

    public function testNotInAcceptsListOfValidUuids(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $expr = $field->notIn($this->queryBuilder, self::VALID_UUID . ',' . self::VALID_UUID_2);

        self::assertSame(
            "c.id NOT IN('" . self::VALID_UUID . "', '" . self::VALID_UUID_2 . "')",
            (string) $expr
        );
    }

    public function testInThrowsForInvalidUuidInList(): void
    {
        $field = new UuidDataField('id', 'c.id');

        $this->expectException(FilterParseException::class);

        $field->in($this->queryBuilder, self::VALID_UUID . ',not-a-uuid');
    }
}
