<?php declare(strict_types=1);

namespace Ekrouzek\FiltersBundle\Pagination;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Ekrouzek\FiltersBundle\QueryFilter\Exception\PaginationAndFilterException;
use Ekrouzek\FiltersBundle\QueryFilter\QueryFilter;
use Doctrine\ORM\QueryBuilder;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Nette\Utils\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as OrmPaginator;

class PaginationHandler
{
    private Paginator $paginator;
    private ?QueryFilter $queryFilter;

    public function __construct(private ParamFetcher $paramFetcher)
    {
        $this->paginator = new Paginator();
    }

    /**
     * Get a query result that is filtered, sorted and paginated.
     * @param QueryBuilder $queryBuilder The prepared query to process.
     * @return array The executed query after filter, sort and pagination were applied.
     * @throws PaginationAndFilterException
     */
    public function getPaginatedData(QueryBuilder $queryBuilder): array
    {
        if ($this->queryFilter !== null) {
            if ($this->paramFetcher->get('filter') !== "") {
                $queryBuilder = $this->queryFilter->filter($queryBuilder, $this->paramFetcher);
            }

            $queryBuilder = $this->queryFilter->sort($queryBuilder, $this->paramFetcher);
        }

        /** @var int $page */
        $page = (int) $this->paramFetcher->get('page');
        /** @var int $itemsPerPage */
        $itemsPerPage = (int) $this->paramFetcher->get('itemsPerPage');
        $itemCount = count($queryBuilder->getQuery()->getResult());

        $this->paginator->setPage($page);
        $this->paginator->setItemsPerPage($itemsPerPage);
        $this->paginator->setItemCount($itemCount);

        $queryBuilder->setFirstResult($this->paginator->getOffset());
        $queryBuilder->setMaxResults($this->paginator->getLength());


        //Set query hydration mode
        $queryBuilder->getQuery()->setHydrationMode(AbstractQuery::HYDRATE_ARRAY);
        $ormPaginator = new OrmPaginator($queryBuilder->getQuery());
        try {
            return $ormPaginator->getIterator()->getArrayCopy();
        } catch (\Exception $e) {
            throw new PaginationAndFilterException("Unexpected error while paginating.");
        }
    }

    /**
     * Get the paginator for returning current page, items per page and item count.
     * This method should be called after @see getPaginatedData() which fills it with data.
     * @return Paginator The used paginator object.
     */
    public function getPaginator(): Paginator
    {
        return $this->paginator;
    }

    /**
     * Sets the configuration for the query filter.
     * Configures which keys are mapped to which database fields and the type of these fields.
     * ```php
     * $paginationHandler->createQueryFilter()
     *     ->addNumberField("id", "c.id")
     *     ->addTextField("name", "c.name")
     *     ->addDatetimeField("created", "c.created")
     *     ->addTextField("address.name", "a.name");
     * ```
     * @return QueryFilter The newly created query filter object to be used for further configuration.
     */
    public function createQueryFilter(): QueryFilter
    {
        $this->queryFilter = new QueryFilter();
        return $this->queryFilter;
    }

    /**
     * Creates a header for the pagination
     * @param array $items The data that should be displayed.
     */
    public function sendPaginatedResponse(array $items): View
    {
        $result = [
            '_pagination' => [
                'total' => $this->getPaginator()->getItemCount(),
                'page' => $this->getPaginator()->getPage(),
                'per_page' => $this->getPaginator()->getItemsPerPage(),
            ],
            'items' => $items,
        ];
        return View::create($result);
    }
}
