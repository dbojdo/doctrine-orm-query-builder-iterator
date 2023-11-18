<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * Iterates over given query using pre-fetched IDs list.
 */
final class IdsIterator extends AbstractIterator
{
    /** @var int[]|string[]|null  */
    private ?array $ids = null;

    public function __construct(
        QueryBuilder $queryBuilder,
        private readonly string $idField,
        int $batchSize = self::DEFAULT_BATCH_SIZE,
        array $clearEntities = null
    ) {
        parent::__construct($queryBuilder, $batchSize, $clearEntities);
    }

    public function rewind(): void
    {
        parent::rewind();
        $this->ids = null;
    }

    protected function loadNextBatch(): array
    {
        if ($this->ids === null) {
            $qb = clone $this->queryBuilder;
            $qb->select('DISTINCT ' . $this->idField);
            $this->ids = $qb->getQuery()->getResult(Query::HYDRATE_SCALAR_COLUMN);
        }

        $currentIds = array_slice($this->ids, $this->getTotalPosition(), $this->getBatchSize());
        if (!$currentIds) {
            return [];
        }

        $qb = $this->prepareBatchQueryBuilder($currentIds);

        $currentResults = $qb->getQuery()->execute();
        return $this->sort($currentResults, $currentIds);
    }

    private function prepareBatchQueryBuilder(array $currentIds): QueryBuilder
    {
        $qb = clone $this->queryBuilder;
        $qb->where(
            $this->queryBuilder->expr()->in(
                $this->idField,
                $currentIds,
            )
        );

        /** @var Query\Expr\From[] $from */
        $from = $qb->getDQLPart('from');
        $from[0] = new Query\Expr\From($from[0]->getFrom(), $from[0]->getAlias(), $this->idField);
        $qb->resetDQLPart('from');
        foreach ($from as $f) {
            $qb->from($f->getFrom(), $f->getAlias(), $f->getIndexBy());
        }

        $qb->resetDQLPart('orderBy');

        return $qb;
    }

    private function sort(array $currentResults, array $currentIds): array
    {
        $currentBatch = [];
        foreach ($currentIds as $id) {
            $currentBatch[] = $currentResults[$id];
        }

        return $currentBatch;
    }
}
