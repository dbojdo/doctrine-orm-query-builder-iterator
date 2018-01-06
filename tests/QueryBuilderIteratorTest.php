<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderIteratorTest extends TestCase
{
    /** @var QueryBuilder|\Mockery\MockInterface */
    private $queryBuilder;

    protected function setUp()
    {
        $this->queryBuilder = \Mockery::mock('Doctrine\ORM\QueryBuilder');
    }

    /**
     * @test
     */
    public function itIteratesOverQueryBuilderResults()
    {
        $iterator = new QueryBuilderIterator($this->queryBuilder, $batchSize = 2, array('\stdClass'));

        $variants = array(
            new \stdClass(),
            new \stdClass(),
            new \stdClass()
        );

        $batch1 = array_slice($variants, 0, 2);
        $batch2 = array_slice($variants, 2);

        $query1 = \Mockery::mock('Doctrine\ORM\AbstractQuery');
        $query2 = \Mockery::mock('Doctrine\ORM\AbstractQuery');

        $this->queryBuilder->shouldReceive('setFirstResult')->with(0)->ordered('limit-offset');
        $this->queryBuilder->shouldReceive('setMaxResults')->with($batchSize)->ordered('limit-offset');

        $this->queryBuilder->shouldReceive('setFirstResult')->with(2)->ordered('limit-offset');
        $this->queryBuilder->shouldReceive('setMaxResults')->with($batchSize)->ordered('limit-offset');

        $this->queryBuilder->shouldReceive('getQuery')->andReturn($query1, $query2);
        $query1->shouldReceive('execute')->andReturn($batch1);
        $query2->shouldReceive('execute')->andReturn($batch2);

        $entityManager = \Mockery::mock('Doctrine\ORM\EntityManagerInterface');
        $entityManager->shouldReceive('clear')->with('\stdClass')->times(3);

        $this->queryBuilder->shouldReceive('getEntityManager')->andReturn($entityManager);

        foreach ($iterator as $k => $variant) {
            $this->assertSame($variants[$k], $variant);
        }
    }

    /**
     * @test
     */
    public function itDoesNotClearEntityManagerIfConfiguredSo()
    {
        $iterator = new QueryBuilderIterator($this->queryBuilder, $batchSize = 2);

        $variants = array(
            new \stdClass(),
            new \stdClass(),
            new \stdClass()
        );

        $batch1 = array_slice($variants, 0, 2);
        $batch2 = array_slice($variants, 2);

        $query1 = \Mockery::mock('Doctrine\ORM\AbstractQuery');
        $query2 = \Mockery::mock('Doctrine\ORM\AbstractQuery');

        $this->queryBuilder->shouldReceive('setFirstResult')->with(0)->ordered('limit-offset');
        $this->queryBuilder->shouldReceive('setMaxResults')->with($batchSize)->ordered('limit-offset');

        $this->queryBuilder->shouldReceive('setFirstResult')->with(2)->ordered('limit-offset');
        $this->queryBuilder->shouldReceive('setMaxResults')->with($batchSize)->ordered('limit-offset');

        $this->queryBuilder->shouldReceive('getQuery')->andReturn($query1, $query2);
        $query1->shouldReceive('execute')->andReturn($batch1);
        $query2->shouldReceive('execute')->andReturn($batch2);

        $this->queryBuilder->shouldReceive('getEntityManager')->never();

        foreach ($iterator as $k => $variant) {
            $this->assertSame($variants[$k], $variant);
        }
    }
}
