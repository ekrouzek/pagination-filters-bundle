<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\DataField;

use Doctrine\ORM\QueryBuilder;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\TextDataField;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

/**
 * Exercises the default DataField behavior (isNull/isNotNull/isMemberOf) that no
 * subtype overrides, via a concrete subtype since DataField itself is abstract.
 */
class DataFieldTest extends TestCase
{
    private QueryBuilder $queryBuilder;

    protected function setUp(): void
    {
        $this->queryBuilder = EntityManagerFactory::create()->createQueryBuilder();
    }

    public function testIsNull(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->isNull($this->queryBuilder);

        self::assertSame('c.name IS NULL', (string) $expr);
    }

    public function testIsNotNull(): void
    {
        $field = new TextDataField('name', 'c.name');

        $expr = $field->isNotNull($this->queryBuilder);

        self::assertSame('c.name IS NOT NULL', (string) $expr);
    }

    public function testIsMemberOf(): void
    {
        $field = new TextDataField('tags', 'c.tags');

        $expr = $field->isMemberOf($this->queryBuilder, '1');

        self::assertSame('1 MEMBER OF c.tags', (string) $expr);
    }

    public function testGetKeyAndGetDbKey(): void
    {
        $field = new TextDataField('name', 'c.name');

        self::assertSame('name', $field->getKey());
        self::assertSame('c.name', $field->getDbKey());
    }
}
