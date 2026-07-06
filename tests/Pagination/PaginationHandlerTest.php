<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Pagination;

use Doctrine\ORM\EntityManager;
use Ekrouzek\PaginationFiltersBundle\Pagination\PaginationHandler;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\ArrayParamFetcher;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\Entity\Course;
use Ekrouzek\PaginationFiltersBundle\Tests\Fixtures\EntityManagerFactory;
use PHPUnit\Framework\TestCase;

class PaginationHandlerTest extends TestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = EntityManagerFactory::create();

        $this->entityManager->persist(new Course('Algebra', new \DateTime('2020-01-01'), true));
        $this->entityManager->persist(new Course('Biology', new \DateTime('2020-02-01'), true));
        $this->entityManager->persist(new Course('Chemistry', new \DateTime('2020-03-01'), false));
        $this->entityManager->flush();
    }

    private function createQueryBuilder(): \Doctrine\ORM\QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(Course::class, 'c');
    }

    public function testGetPaginatedDataAppliesFilterSortAndPaging(): void
    {
        $paramFetcher = new ArrayParamFetcher([
            'page' => 1,
            'itemsPerPage' => 10,
            'filter' => 'eq:active:1',
            'sort' => 'name:desc',
        ]);

        $handler = new PaginationHandler($paramFetcher);
        $handler->createQueryFilter()
            ->addTextField('name', 'c.name')
            ->addBooleanField('active', 'c.active');

        $result = $handler->getPaginatedData($this->createQueryBuilder());

        self::assertCount(2, $result);
        self::assertSame('Biology', $result[0]->getName());
        self::assertSame('Algebra', $result[1]->getName());
        self::assertSame(2, $handler->getPaginator()->getItemCount());
        self::assertSame(1, $handler->getPaginator()->getPage());
    }

    public function testGetPaginatedDataAppliesPagingWithoutFilter(): void
    {
        $paramFetcher = new ArrayParamFetcher([
            'page' => 2,
            'itemsPerPage' => 2,
            'filter' => '',
            'sort' => 'name:asc',
        ]);

        $handler = new PaginationHandler($paramFetcher);
        $handler->createQueryFilter()->addTextField('name', 'c.name');

        $result = $handler->getPaginatedData($this->createQueryBuilder());

        self::assertCount(1, $result);
        self::assertSame('Chemistry', $result[0]->getName());
        self::assertSame(3, $handler->getPaginator()->getItemCount());
    }

    public function testGetPaginatedDataWithoutCreateQueryFilterCall(): void
    {
        $paramFetcher = new ArrayParamFetcher([
            'page' => 1,
            'itemsPerPage' => 10,
        ]);

        $handler = new PaginationHandler($paramFetcher);

        $result = $handler->getPaginatedData($this->createQueryBuilder());

        self::assertCount(3, $result);
    }

    public function testSendPaginatedResponseWrapsItemsWithPaginationHeader(): void
    {
        $paramFetcher = new ArrayParamFetcher([
            'page' => 1,
            'itemsPerPage' => 2,
            'filter' => '',
            'sort' => '',
        ]);

        $handler = new PaginationHandler($paramFetcher);
        $handler->createQueryFilter()->addTextField('name', 'c.name');
        $items = $handler->getPaginatedData($this->createQueryBuilder());

        $view = $handler->sendPaginatedResponse($items);

        self::assertSame([
            '_pagination' => [
                'total' => 3,
                'page' => 1,
                'per_page' => 2,
            ],
            'items' => $items,
        ], $view->getData());
    }

    public function testGetPaginatedResponseDataReturnsSameStructureWithoutView(): void
    {
        $paramFetcher = new ArrayParamFetcher([
            'page' => 1,
            'itemsPerPage' => 2,
            'filter' => '',
            'sort' => '',
        ]);

        $handler = new PaginationHandler($paramFetcher);
        $handler->createQueryFilter()->addTextField('name', 'c.name');
        $items = $handler->getPaginatedData($this->createQueryBuilder());

        $data = $handler->getPaginatedResponseData($items);

        self::assertSame([
            '_pagination' => [
                'total' => 3,
                'page' => 1,
                'per_page' => 2,
            ],
            'items' => $items,
        ], $data);
    }
}
