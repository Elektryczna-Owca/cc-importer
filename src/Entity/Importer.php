<?php

namespace App\Entity;

use App\Repository\ImporterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'Importer')]
#[ORM\Entity(repositoryClass: ImporterRepository::class)]
class Importer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'name', length: 64, nullable: true)]
    private ?string $name = null;

    public ?string $fileName = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }   

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }  
}
