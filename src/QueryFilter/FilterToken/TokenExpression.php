<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\FilterToken;

use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\FilterParseException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception\PaginationAndFilterException;
use Ekrouzek\PaginationFiltersBundle\QueryFilter\QueryFilter;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;

class TokenExpression extends FilterToken
{

    /** @inheritDoc */
    public function getPriority(): int
    {
        return self::PRIORITY_EXPRESSION;
    }

    /**
     * Creates the final expression object. Specifically the Comparison object.
     * Checks the validity of the expression string.
     *
     * The arguments $exprLeft and $exprRight should be null at this point.
     *
     * @inheritDoc
     * @throws PaginationAndFilterException If the parsing of the filter string is unsuccessful or the data passed are invalid.
     */
    public function combineExpr(QueryBuilder $queryBuilder, mixed $exprLeft, mixed $exprRight, array &$dataFields): mixed
    {
        $parts = explode(":", $this->value);
        $partsLen = count($parts);
        if ($partsLen < QueryFilter::FILTER_FORMAT_PARTS_NMB) {
            throw new FilterParseException("An expression string doesn't have " . QueryFilter::FILTER_FORMAT_PARTS_NMB . " required parts.");
        }
        if ($partsLen > QueryFilter::FILTER_FORMAT_PARTS_NMB) { // Unite remaining parts to the third slot
            $unitedThirdSlot = [];
            for ($i = QueryFilter::FILTER_FORMAT_PARTS_NMB - 1; $i < $partsLen; $i++) {
                $unitedThirdSlot[] = $parts[$i];
            }
            $parts[2] = implode(':', $unitedThirdSlot);
        }
        if (!array_key_exists($parts[1], $dataFields)) {
            throw new FilterParseException("An expression key '$parts[1]' does not exist.");
        }
        switch ($parts[0]) {
            case 'eq':
                return $dataFields[$parts[1]]->eq($queryBuilder, $parts[2]);
            case 'neq':
                return $dataFields[$parts[1]]->neq($queryBuilder, $parts[2]);
            case 'like':
                return $dataFields[$parts[1]]->like($queryBuilder, $parts[2]);
            case 'not-like':
                return $dataFields[$parts[1]]->notLike($queryBuilder, $parts[2]);
            case 'lt':
                return $dataFields[$parts[1]]->lt($queryBuilder, $parts[2]);
            case 'lte':
                return $dataFields[$parts[1]]->lte($queryBuilder, $parts[2]);
            case 'gt':
                return $dataFields[$parts[1]]->gt($queryBuilder, $parts[2]);
            case 'gte':
                return $dataFields[$parts[1]]->gte($queryBuilder, $parts[2]);
            case 'is-null':
                return $dataFields[$parts[1]]->isNull($queryBuilder);
            case 'is-not-null':
                return $dataFields[$parts[1]]->isNotNull($queryBuilder);
            case 'is-member-of':
                return $dataFields[$parts[1]]->isMemberOf($queryBuilder, $parts[2]);
        }
        throw new FilterParseException("Unsupported filter operation: '$parts[0]'");
    }
}
