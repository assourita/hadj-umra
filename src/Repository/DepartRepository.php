<?php

namespace App\Repository;

use App\Entity\Depart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Depart>
 */
class DepartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depart::class);
    }

    /**
     * Départs actifs avec places disponibles
     */
    public function findAvailableDeparts(): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.package', 'p')
            ->leftJoin('d.tarifs', 't')
            ->addSelect('p', 't')
            ->andWhere('d.isActive = :active')
            ->andWhere('p.isActive = :active')
            ->andWhere('d.dateDepart > :now')
            ->andWhere('d.quotaVendu < d.quotaTotal')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('d.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Départs par package
     */
    public function findByPackage(int $packageId): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.tarifs', 't')
            ->addSelect('t')
            ->andWhere('d.package = :packageId')
            ->andWhere('d.isActive = :active')
            ->setParameter('packageId', $packageId)
            ->setParameter('active', true)
            ->orderBy('d.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Départs prochains (dans les 30 jours)
     */
    public function findUpcomingDeparts(): array
    {
        $now = new \DateTimeImmutable();
        $in30Days = $now->modify('+30 days');

        return $this->createQueryBuilder('d')
            ->leftJoin('d.package', 'p')
            ->addSelect('p')
            ->andWhere('d.isActive = :active')
            ->andWhere('d.dateDepart BETWEEN :now AND :in30days')
            ->setParameter('active', true)
            ->setParameter('now', $now)
            ->setParameter('in30days', $in30Days)
            ->orderBy('d.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Départs par ville
     */
    public function findByVille(string $ville): array
    {
        return $this->createQueryBuilder('d')
            ->leftJoin('d.package', 'p')
            ->addSelect('p')
            ->andWhere('d.villeDepart LIKE :ville')
            ->andWhere('d.isActive = :active')
            ->andWhere('d.dateDepart > :now')
            ->setParameter('ville', '%' . $ville . '%')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('d.dateDepart', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 