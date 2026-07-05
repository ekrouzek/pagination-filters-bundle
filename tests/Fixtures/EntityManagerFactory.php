<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Fixtures;

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Builds a real, in-memory SQLite-backed EntityManager for tests, so QueryBuilder/DQL
 * produced by the bundle can be checked against actual Doctrine behaviour instead of mocks.
 */
class EntityManagerFactory
{
    public static function create(): EntityManager
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/Entity'],
            isDevMode: true,
        );

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

        return $entityManager;
    }
}
