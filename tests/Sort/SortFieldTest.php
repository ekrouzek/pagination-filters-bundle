<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Sort;

use Ekrouzek\PaginationFiltersBundle\Sort\SortField;
use PHPUnit\Framework\TestCase;

class SortFieldTest extends TestCase
{
    public function testGetKeyAndGetDirection(): void
    {
        $sortField = new SortField('name', SortField::DESC);

        self::assertSame('name', $sortField->getKey());
        self::assertSame('desc', $sortField->getDirection());
    }
}
