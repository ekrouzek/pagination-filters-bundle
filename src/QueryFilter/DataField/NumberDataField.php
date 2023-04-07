<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\DataField;

use Ekrouzek\FiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\FiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
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
}
