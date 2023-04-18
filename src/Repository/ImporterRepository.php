<?php

namespace App\Repository;

use App\Entity\Importer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Importer>
 *
 * @method Importer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Importer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Importer[]    findAll()
 * @method Importer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImporterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Importer::class);
    }

    public function save(Importer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Importer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
