<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\FilterToken;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class TokenOr extends FilterToken
{

    /** @inheritDoc */
    public function getPriority(): int
    {
        return self::PRIORITY_OR;
    }

    /**
     * @inheritDoc
     */
    public function combineExpr(QueryBuilder $queryBuilder, mixed $exprLeft, mixed $exprRight, array &$dataFields): mixed
    {
        return $queryBuilder->expr()->orX(
            $exprLeft,
            $exprRight
        );
    }
}
