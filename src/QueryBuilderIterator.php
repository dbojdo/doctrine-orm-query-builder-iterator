<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Doctrine\ORM\QueryBuilder;

final class QueryBuilderIterator implements \Iterator
{
    /** @var QueryBuilder */
    private $queryBuilder;

    /** @var int */
    private $batchSize;

    /** @var array */
    private $clearEntities;

    /** @var object[] */
    private $currentBatch;

    /** @var int */
    private $currentBatchPosition = 0;

    /** @var int */
    private $totalPosition = 0;
    
    public function __construct(QueryBuilder $queryBuilder, $batchSize = 50, array $clearEntities = array())
    {
        $this->queryBuilder = $queryBuilder;
        $this->batchSize = $batchSize;
        $this->clearEntities = $clearEntities;
    }

    public function current()
    {
        $this->loadNextBatch();
        if (isset($this->currentBatch[$this->currentBatchPosition])) {
            return $this->currentBatch[$this->currentBatchPosition];
        }

        return null;
    }

    public function next()
    {
        $this->currentBatchPosition++;
        $this->totalPosition++;
    }

    public function key()
    {
        return $this->totalPosition;
    }

    public function valid()
    {
        if ($this->currentBatch === null || $this->currentBatchPosition > $this->batchSize - 1) {
            $this->loadNextBatch(true);
        }

        return (bool)count($this->currentBatch) && $this->currentBatchPosition < count($this->currentBatch);
    }

    public function rewind()
    {
        $this->currentBatch = null;
        $this->currentBatchPosition = 0;
        $this->totalPosition = 0;
        $this->clearEntityManager();
    }

    private function loadNextBatch($forceLoad = false)
    {
        if (!($this->currentBatch === null || $forceLoad)) {
            return;
        }

        $this->clearEntityManager();

        $this->queryBuilder->setFirstResult($this->totalPosition);
        $this->queryBuilder->setMaxResults($this->batchSize);

        $this->currentBatch = $this->queryBuilder->getQuery()->execute();
        $this->currentBatchPosition = 0;
    }

    private function clearEntityManager()
    {
        foreach ($this->clearEntities as $entityName) {
            $this->queryBuilder->getEntityManager()->clear($entityName);
        }
    }
}
