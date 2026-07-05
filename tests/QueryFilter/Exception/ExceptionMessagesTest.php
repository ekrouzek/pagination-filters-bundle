<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\QueryFilter\Exception;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\TextDataField;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\SortParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use PHPUnit\Framework\TestCase;

class ExceptionMessagesTest extends TestCase
{
    public function testFilterParseExceptionPrefixesNonEmptyMessage(): void
    {
        $exception = new FilterParseException('something went wrong');

        self::assertSame('Query filter: something went wrong', $exception->getMessage());
    }

    public function testFilterParseExceptionLeavesEmptyMessageUnprefixed(): void
    {
        $exception = new FilterParseException();

        self::assertSame('', $exception->getMessage());
    }

    public function testSortParseExceptionPrefixesNonEmptyMessage(): void
    {
        $exception = new SortParseException('something went wrong');

        self::assertSame('Query sort: something went wrong', $exception->getMessage());
    }

    public function testSortParseExceptionLeavesEmptyMessageUnprefixed(): void
    {
        $exception = new SortParseException();

        self::assertSame('', $exception->getMessage());
    }

    public function testUnsupportedDataFieldMethodExceptionMessageIncludesFieldNameAndKey(): void
    {
        $field = new TextDataField('name', 'c.name');

        $exception = new UnsupportedDataFieldMethodException('lt', $field);

        self::assertSame(
            "Query filter: Method 'lt' isn't supported for text data field 'name'.",
            $exception->getMessage()
        );
    }
}
