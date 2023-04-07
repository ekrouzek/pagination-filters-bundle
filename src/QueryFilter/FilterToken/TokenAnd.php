<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\FilterToken;

use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class TokenAnd extends FilterToken
{

    /** @inheritDoc */
    public function getPriority(): int
    {
        return self::PRIORITY_AND;
    }

    /**
     * @inheritDoc
     */
    public function combineExpr(QueryBuilder $queryBuilder, mixed $exprLeft, mixed $exprRight, array &$dataFields): mixed
    {
        return $queryBuilder->expr()->andX(
            $exprLeft,
            $exprRight
        );
    }
}
