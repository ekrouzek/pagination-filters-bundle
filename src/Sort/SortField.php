<?php

namespace Ekrouzek\PaginationFiltersBundle\Sort;

class SortField
{
    public const ASC = "asc";
    public const DESC = "desc";

    public function __construct(private string $key, private string $direction)
    {
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }
}