<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\FilterToken;

class TokenBracketLeft extends FilterToken
{

    /** @inheritDoc */
    public function getPriority(): int
    {
        return self::PRIORITY_BRACKET;
    }
}
