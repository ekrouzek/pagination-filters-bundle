<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Uid\Uuid;

class UuidDataField extends DataField
{
    /** @inheritDoc */
    public function getName(): string
    {
        return "uuid";
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't a valid UUID.
     */
    public function eq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return parent::eq($queryBuilder, $this->stripQuotesAndValidate($right));
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't a valid UUID.
     */
    public function neq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return parent::neq($queryBuilder, $this->stripQuotesAndValidate($right));
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function like(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("like", $this);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function notLike(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("not-like", $this);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function lt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("lt", $this);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function lte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("lte", $this);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function gt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("gt", $this);
    }

    /**
     * @inheritDoc
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function gte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("gte", $this);
    }

    /**
     * Strips optional surrounding quotes and validates that the value is a well-formed UUID.
     *
     * @param string $right The raw filter value.
     * @return string The unquoted, validated UUID string.
     * @throws FilterParseException If the value isn't a valid UUID.
     */
    private function stripQuotesAndValidate(string $right): string
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!Uuid::isValid($right)) {
            throw new FilterParseException("Value passed to uuid data field is not a valid UUID.");
        }
        return $right;
    }
}
