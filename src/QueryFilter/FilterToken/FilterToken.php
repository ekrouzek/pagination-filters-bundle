<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\FilterToken;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\DataField\DataField;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

abstract class FilterToken
{
    public const PRIORITY_AND = 2;
    public const PRIORITY_OR = 1;
    public const PRIORITY_EXPRESSION = 0;
    public const PRIORITY_BRACKET = 0;

    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }


    /**
     * The priority of the operator/operand that is used while parsing the filter token.
     * @return int The set priority.
     */
    abstract public function getPriority(): int;

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Combines two expressions into single one.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param mixed $exprLeft The left expr object to combine.
     * @param mixed $exprRight The right expr object to combine.
     * @param array<DataField> &$dataFields The array of configured data fields for translation.
     * @return Andx|Orx|Comparison|null The combined expr object.
     */
    public function combineExpr(QueryBuilder $queryBuilder, mixed $exprLeft, mixed $exprRight, array &$dataFields): mixed
    {
        return null;
    }
}
