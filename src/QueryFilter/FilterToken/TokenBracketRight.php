<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\FilterToken;

class TokenBracketRight extends FilterToken
{

    /** @inheritDoc */
    public function getPriority(): int
    {
        return self::PRIORITY_BRACKET;
    }
}
