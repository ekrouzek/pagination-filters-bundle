<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\DataField;

use Ekrouzek\FiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class TextDataField extends DataField
{
    /** @inheritDoc */
    public function getName(): string
    {
        return "text";
    }


    /** @inheritDoc */
    public function eq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        return parent::eq($queryBuilder, $right);
    }

    /** @inheritDoc */
    public function neq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        return parent::neq($queryBuilder, $right);
    }

    /** @inheritDoc */
    public function like(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        return parent::like($queryBuilder, $right);
    }

    /** @inheritDoc */
    public function notLike(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        return parent::notLike($queryBuilder, $right);
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
}
