<?php

namespace App\Repository;

use App\Entity\Visa;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visa>
 */
class VisaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visa::class);
    }

    /**
     * @return Visa[]
     */
    public function findAllWithPelerin(): array
    {
        return $this->createQueryBuilder('v')
            ->leftJoin('v.pelerin', 'p')
            ->addSelect('p')
            ->orderBy('v.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
} 