<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Webit\DoctrineORM\QueryBuilder\Iterator\Entity\TestEntity;

class QueryBuilderIteratorTest extends AbstractTestCase
{
    protected function createIterator(): \Iterator
    {
        return new QueryBuilderIterator(
            $this->entityManager
                ->createQueryBuilder()
                ->select('t')
                ->from(TestEntity::class, 't')
                ->orderBy('t.id', 'DESC'),
            $this->batchSize,
            [TestEntity::class],
        );
    }
}
