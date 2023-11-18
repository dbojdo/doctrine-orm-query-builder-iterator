<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Webit\DoctrineORM\QueryBuilder\Iterator\Entity\TestEntity;

class IdsIteratorTest extends AbstractTestCase
{
    protected function createIterator(): \Iterator
    {
        return new IdsIterator(
            $this->entityManager
                ->createQueryBuilder()
                ->select('t')
                ->from(TestEntity::class, 't')
                ->orderBy('t.id', 'DESC'),
            't.id',
            $this->batchSize,
            [TestEntity::class],
        );
    }
}
