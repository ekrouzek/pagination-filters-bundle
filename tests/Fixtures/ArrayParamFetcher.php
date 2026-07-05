<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Fixtures;

use FOS\RestBundle\Request\ParamFetcherInterface;

/**
 * Minimal ParamFetcherInterface test double backed by a plain array.
 * QueryFilter/PaginationHandler only ever call get(), so that's all that's implemented meaningfully.
 */
class ArrayParamFetcher implements ParamFetcherInterface
{
    /**
     * @param array<string, mixed> $params
     */
    public function __construct(private array $params)
    {
    }

    public function setController(callable $controller): void
    {
    }

    public function get(string $name, ?bool $strict = null)
    {
        return $this->params[$name] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(bool $strict = false)
    {
        return $this->params;
    }
}
