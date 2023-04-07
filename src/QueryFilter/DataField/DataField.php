<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\DataField;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

abstract class DataField
{
    protected string $key;
    protected string $dbKey;

    public function __construct(string $key, string $dbKey)
    {
        $this->key = $key;
        $this->dbKey = $dbKey;
    }

    /**
     * Get the name of the data field.
     * Used for exceptions.
     *
     * @return string The name of the data field.
     */
    abstract public function getName(): string;

    /**
     * Get the outside key of the field.
     * @return string The key that is used in the filter string.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get the internal DQL key of the field.
     * @return string The key that is used in the DQL query.
     */
    public function getDbKey(): string
    {
        return $this->dbKey;
    }

    /**
     * Creates an equality comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function eq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->eq($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates a non-equality comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function neq(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->neq($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates a like comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function like(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->like("LOWER(" . $this->dbKey . ")", $queryBuilder->expr()->literal('%' . strtolower($right) . '%'));
    }

    /**
     * Creates a not-like comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function notLike(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->notLike($this->dbKey, $queryBuilder->expr()->literal('%' . $right . '%'));
    }

    /**
     * Creates a lower than comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function lt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->lt($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates a lower than or equal comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function lte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->lte($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates a greater than comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function gt(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->gt($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates a greater than or equal comparison expression with the given arguments.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @param string $right The value of the expression (the right operand).
     * @return Andx|Orx|Comparison|null The created expr object.
     */
    public function gte(QueryBuilder $queryBuilder, string $right): Andx|Orx|Comparison|null
    {
        return $queryBuilder->expr()->gte($this->dbKey, $queryBuilder->expr()->literal($right));
    }

    /**
     * Creates an is null expression for the given key.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @return string The created expr object.
     */
    public function isNull(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->expr()->isNull($this->dbKey);
    }

    /**
     * Creates an is not null expression for the given key.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @return string The created expr object.
     */
    public function isNotNull(QueryBuilder $queryBuilder): string
    {
        return $queryBuilder->expr()->isNotNull($this->dbKey);
    }

    /**
     * Creates an is member of expression for the given key and value.
     *
     * @param QueryBuilder $queryBuilder The query builder to create expr objects.
     * @return Comparison The created expr object.
     */
    public function isMemberOf(QueryBuilder $queryBuilder, string $right): Comparison
    {
        return $queryBuilder->expr()->isMemberOf($right, $this->dbKey);
    }
}
