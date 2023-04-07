<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\QueryFilter\Exception;

use Throwable;

class SortParseException extends PaginationAndFilterException
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        if ($message !== "") {
            $message = "Query sort: $message";
        }
        parent::__construct($message, $code, $previous);
    }
}
