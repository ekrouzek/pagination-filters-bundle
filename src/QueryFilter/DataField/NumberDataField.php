<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class NumberDataField extends DataField
{
    /** @inheritDoc */
    public function getName(): string
    {
        return "number";
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function eq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::eq($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function neq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::neq($queryBuilder, $right);
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
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this datafield.
     */
    public function notLike(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("not-like", $this);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function lt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::lt($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function lte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::lte($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function gt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::gt($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't numeric.
     */
    public function gte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (!is_numeric($right)) {
            throw new FilterParseException("Value passed to number data field is non-numeric.");
        }
        return parent::gte($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If any value in the list isn't numeric.
     */
    public function in(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|Func|null
    {
        return $this->buildInExpr($queryBuilder, $this->validateAllNumeric($this->parseListValues($right)), false);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If any value in the list isn't numeric.
     */
    public function notIn(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|Func|null
    {
        return $this->buildInExpr($queryBuilder, $this->validateAllNumeric($this->parseListValues($right)), true);
    }

    /**
     * Checks that every value in the given list is numeric.
     *
     * @param array<string> $values The values to check.
     * @return array<string> The same values, if valid.
     * @throws FilterParseException If any value isn't numeric.
     */
    private function validateAllNumeric(array $values): array
    {
        foreach ($values as $value) {
            if (!is_numeric($value)) {
                throw new FilterParseException("Value passed to number data field is non-numeric.");
            }
        }
        return $values;
    }
}
