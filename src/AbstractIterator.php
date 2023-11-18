<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator;

use Doctrine\ORM\Query\Expr\From;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

abstract class AbstractIterator implements \Iterator
{
    public const DEFAULT_BATCH_SIZE = 50;

    private readonly array $clearEntities;

    /** @var object[]|null */
    private ?array $currentBatch;

    private int $currentBatchPosition = 0;

    private int $totalPosition = 0;

    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly int $batchSize = self::DEFAULT_BATCH_SIZE,
        array $clearEntities = null
    ) {
        $this->clearEntities = $clearEntities ?? $this->resolveClearEntities();
    }


    public function current(): ?object
    {
        $this->nextBatch();
        if (isset($this->currentBatch[$this->currentBatchPosition])) {
            return $this->currentBatch[$this->currentBatchPosition];
        }

        return null;
    }

    public function next(): void
    {
        $this->currentBatchPosition++;
        $this->totalPosition++;
    }

    public function key(): int
    {
        return $this->totalPosition;
    }

    public function valid(): bool
    {
        if ($this->currentBatch === null || $this->currentBatchPosition > $this->batchSize - 1) {
            $this->nextBatch(true);
        }

        return count($this->currentBatch) && $this->currentBatchPosition < count($this->currentBatch);
    }

    public function rewind(): void
    {
        $this->currentBatch = null;
        $this->currentBatchPosition = 0;
        $this->totalPosition = 0;
        $this->clearEntityManager();
    }

    protected function getTotalPosition(): int
    {
        return $this->totalPosition;
    }

    protected function getBatchSize(): int
    {
        return $this->batchSize;
    }

    protected function clearEntityManager(): void
    {
        $em = $this->queryBuilder->getEntityManager();
        foreach ($this->clearEntities as $entity) {
            $em->clear($entity);
        }
    }

    protected function resolveClearEntities(): array
    {
        return array_unique(
            array_merge(
                array_map(
                    fn (Join $join) => $join->getJoin(),
                    $this->queryBuilder->getDQLPart('join'),
                ),
                array_map(
                    fn (From $from) => $from->getFrom(),
                    $this->queryBuilder->getDQLPart('from')
                )
            )
        );
    }

    private function nextBatch(bool $forceLoad = false): void
    {
        if (!($this->currentBatch === null || $forceLoad)) {
            return;
        }

        $this->clearEntityManager();

        $this->currentBatchPosition = 0;
        $this->currentBatch = $this->loadNextBatch();
    }

    protected abstract function loadNextBatch(): array;
}
