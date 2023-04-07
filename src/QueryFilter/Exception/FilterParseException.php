<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\Exception;

use Throwable;

class FilterParseException extends PaginationAndFilterException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if ($message !== "") {
            $message = "Query filter: $message";
        }
        parent::__construct($message, $code, $previous);
    }
}