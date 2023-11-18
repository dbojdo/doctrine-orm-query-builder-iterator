# Doctrine ORM Query Builder Iterator
Allows to iterate over Query Builder Results in batches

## Installation

```bash
composer require webit/doctrine-orm-query-builder-iterator=^2.0.0
```

## Usage

### QueryIterator implementation

This implementation iterates over given query using limit / offset parameters.

```php
<?php
use Webit\DoctrineORM\QueryBuilder\Iterator\QueryIterator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$queryBuilder = $entityManager->getRepository('MyEntity')->createQueryBuilder();
$queryBuilder->orderBy('a.ble', 'DESC');

$iterator = new QueryIterator(
    $queryBuilder,
    20, // iterates in 50 elements batches (50 by default)
    ['MyEntity'], // clears entity manager before getting next batch (by default clears all involved in the query) 
);

foreach ($iterator as $entity) {
    // do your stuff with the entity
}

```


### IdsIterator implementation

This implementation iterates over given query using pre-fetched list of identifiers.

```php
<?php
use Webit\DoctrineORM\QueryBuilder\Iterator\IdsIterator;

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */

$queryBuilder = $entityManager->getRepository('MyEntity')->createQueryBuilder();
$queryBuilder->orderBy('a.ble', 'DESC');

$iterator = new IdsIterator(
    $queryBuilder,
    'a.id', // the ID field
    20, // iterates in 20 elements batches (50 by default)
    ['MyEntity'], // clears entity manager before getting next batch (by default clears all involved in the query) 
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
