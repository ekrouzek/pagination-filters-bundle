<?php declare(strict_types=1);

namespace Ekrouzek\PaginationFiltersBundle\Tests\Fixtures;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Builds a real, in-memory SQLite-backed EntityManager for tests, so QueryBuilder/DQL
 * produced by the bundle can be checked against actual Doctrine behaviour instead of mocks.
 */
class EntityManagerFactory
{
    public static function create(): EntityManager
    {
        $config = Setup::createAttributeMetadataConfiguration(
            paths: [__DIR__ . '/Entity'],
            isDevMode: true,
        );

        $entityManager = EntityManager::create([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $schemaTool = new SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->createSchema($metadata);

        return $entityManager;
    }
}
