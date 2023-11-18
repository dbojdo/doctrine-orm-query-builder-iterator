<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;
use Webit\DoctrineORM\QueryBuilder\Iterator\Entity\TestEntity;

abstract class AbstractTestCase extends TestCase
{
    private string $dbPath;

    protected EntityManager $entityManager;

    protected int $totalEntities;
    protected int $batchSize;

    protected function setUp(): void
    {
        $this->totalEntities = mt_rand(22, 35);
        $this->batchSize = mt_rand(5, 15);

        list (
            $this->dbPath,
            $this->entityManager,
        ) = $this->prepareSchema();
    }

    private function prepareSchema(): array
    {
        $dbPath = sprintf(__DIR__.'/../.phpunit/test-db-%s.sqlite', Uuid::v4());

        $dsnParser = new DsnParser();
        $connectionParams = $dsnParser
            ->parse('pdo-sqlite:///'.$dbPath);

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: array(__DIR__."/tests/Entity"),
            isDevMode: true,
        );

        $entityManager = new EntityManager(
            DriverManager::getConnection($connectionParams, $config),
            $config,
        );

        $tools = new SchemaTool($entityManager);
        $tools->createSchema([$entityManager->getClassMetadata(TestEntity::class)]);

        for ($i = 0; $i < $this->totalEntities; $i++) {
            $entityManager->persist(
                new TestEntity(),
            );
        }
        $entityManager->flush();
        $entityManager->clear();

        return [$dbPath, $entityManager];
    }

    abstract protected function createIterator(): \Iterator;

    #[Test]
    public function itIteratesOverQuery(): void
    {
        $iterator = $this->createIterator();

        $ids = [];
        foreach ($iterator as $testEntity) {
            $ids[] = $testEntity->id;
        }
        $idsSorted = $ids;
        rsort($idsSorted);

        $this->assertEquals($idsSorted, $ids);
        $this->assertSame($this->totalEntities, count($ids));
    }

    protected function tearDown(): void
    {
        @unlink($this->dbPath);
    }
}
