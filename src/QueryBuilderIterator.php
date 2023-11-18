<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

/**
 * @deprecated Use QueryIterator instead.
 */
class QueryBuilderIterator extends AbstractIterator
{
    protected function loadNextBatch(): array
    {
        $this->queryBuilder->setFirstResult($this->getTotalPosition());
        $this->queryBuilder->setMaxResults($this->getBatchSize());

        return $this->queryBuilder->getQuery()->execute();
    }
}
