<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\QueryFilter\Exception;

use Ekrouzek\FiltersBundle\QueryFilter\DataField\DataField;
use Throwable;

class UnsupportedDataFieldMethodException extends PaginationAndFilterException
{
    public function __construct(string $method, DataField $dataField, int $code = 0, ?Throwable $previous = null)
    {
        $key = $dataField->getKey();
        $fieldName = $dataField->getName();
        $message = "Query filter: Method '$method' isn't supported for $fieldName data field '$key'.";
        parent::__construct($message, $code, $previous);
    }
}
