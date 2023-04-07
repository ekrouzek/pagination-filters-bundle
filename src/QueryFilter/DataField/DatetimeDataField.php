<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\UnsupportedDataFieldMethodException;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class DatetimeDataField extends DataField
{

    /** @inheritDoc */
    public function getName(): string
    {
        return "datetime";
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function eq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
        }
        return parent::eq($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function neq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
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
     * @throws UnsupportedDataFieldMethodException This method isn't supported for this data field.
     */
    public function notLike(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        throw new UnsupportedDataFieldMethodException("not-like", $this);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function lt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
        }
        return parent::lt($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function lte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
        }
        return parent::lte($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function gt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
        }
        return parent::gt($queryBuilder, $right);
    }

    /**
     * @inheritDoc
     * @throws FilterParseException If the value isn't in datetime format.
     */
    public function gte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        if (str_starts_with($right, '"') && str_ends_with($right, '"')) {
            $right = substr($right, 1, -1);
        }
        if (!$this->checkValidDateTime($right)) {
            throw new FilterParseException("Value passed to datetime data field is not in a datetime format.");
        }
        return parent::gte($queryBuilder, $right);
    }

    /**
     * Checks if the passed value is in valid Carbon datetime format.
     *
     * @param string $text The string to check.
     * @return bool True if the value is in datetime format, false otherwise.
     */
    private function checkValidDateTime(string $text): bool
    {
        try {
            $date = Carbon::parse($text);
        } catch (InvalidFormatException $_) {
            return false;
        }
        return true;
    }
}
