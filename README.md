# Doctrine ORM Query Builder Iterator
Allows to iterate over Query Builder Results in batches

## Installation

```bash
composer require webit/doctrine-orm-query-builder-iterator=^1.0.0
```

## Usage

```php
<?php
use Webit\DoctrineORM\QueryBuilder\Iterator\QueryBuilderIterator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$queryBuilder = $entityManager->getRepository('MyEntity')->createQueryBuilder();
$queryBuilder->orderBy('a.ble', 'DESC');

$iterator = new QueryBuilderIterator(
    $queryBuilder,
    20, // iterates in 50 elements batches (50 by default)
    array('MyEntity') // clears entity manager before getting next batch for listed entities (empty by default) 
);

foreach ($iterator as $entity) {
    // do your stuff with the entity
}

```

## Tests

```bash
composer install
./vendor/bin/phpunit
```
