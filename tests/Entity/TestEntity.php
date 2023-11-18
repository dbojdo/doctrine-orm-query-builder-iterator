<?php

namespace Webit\DoctrineORM\QueryBuilder\Iterator\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'test_entities')]
class TestEntity
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: Types::STRING, length: 36, nullable: false)]
    public readonly string $id;

    #[ORM\Column(name: 'name', type: Types::STRING, nullable: false)]
    public readonly string $name;

    public function __construct()
    {
        $this->id = (string) Uuid::v4();
        $this->name = 'test-' . $this->id;
    }
}
