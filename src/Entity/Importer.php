<?php

namespace App\Entity;

use App\Repository\ImporterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Table(name: 'Importer')]
#[ORM\Entity(repositoryClass: ImporterRepository::class)]
class Importer implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'name', length: 64, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'token', length: 256, nullable: true)]
    private ?string $token = null;


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

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function jsonSerialize() {
        return [
            'name' => $this->name
        ];
    }
}
