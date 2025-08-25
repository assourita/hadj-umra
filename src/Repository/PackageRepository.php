<?php

namespace App\Repository;

use App\Entity\Package;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Package>
 */
class PackageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Package::class);
    }

    /**
     * Recherche des packages actifs avec des départs disponibles
     */
    public function findAvailablePackages(): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.departs', 'd')
            ->andWhere('p.isActive = :active')
            ->andWhere('d.isActive = :active')
            ->andWhere('d.dateDepart > :now')
            ->andWhere('d.quotaVendu < d.quotaTotal')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par critères (ville, date, visa inclus)
     */
    public function findByCriteria(?string $city = null, ?\DateTimeImmutable $dateFrom = null, ?bool $visaIncluded = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.departs', 'd')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true);

        // Si on filtre par ville ou date, on s'assure qu'il y a des départs
        if ($city || $dateFrom) {
            $qb->andWhere('d.isActive = :departActive')
               ->setParameter('departActive', true);
        }

        if ($city) {
            $qb->andWhere('d.villeDepart LIKE :city')
               ->setParameter('city', '%' . $city . '%');
        }

        if ($dateFrom) {
            $qb->andWhere('d.dateDepart >= :dateFrom')
               ->setParameter('dateFrom', $dateFrom);
        }

        if ($visaIncluded !== null) {
            if ($visaIncluded) {
                $qb->andWhere('p.inclus LIKE :visa')
                   ->setParameter('visa', '%visa%');
            } else {
                $qb->andWhere('p.inclus NOT LIKE :visa OR p.inclus IS NULL')
                   ->setParameter('visa', '%visa%');
            }
        }

        return $qb->orderBy('p.createdAt', 'DESC')
                  ->getQuery()
                  ->getResult();
    }

    /**
     * Package par slug avec ses départs actifs
     */
    public function findBySlugWithActiveDeparts(string $slug): ?Package
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.departs', 'd')
            ->leftJoin('d.tarifs', 't')
            ->addSelect('d', 't')
            ->andWhere('p.slug = :slug')
            ->andWhere('p.isActive = :active')
            ->setParameter('slug', $slug)
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Packages populaires (avec le plus de réservations)
     */
    public function findPopularPackages(int $limit = 6): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.departs', 'd')
            ->leftJoin('d.reservations', 'r')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->groupBy('p.id')
            ->orderBy('COUNT(r.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Packages récents
     */
    public function findRecentPackages(int $limit = 3): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des packages
     */
    public function getPackageStats(): array
    {
        $total = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.isActive = :active')
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();

        $withDeparts = $this->createQueryBuilder('p')
            ->select('COUNT(DISTINCT p.id)')
            ->leftJoin('p.departs', 'd')
            ->andWhere('p.isActive = :active')
            ->andWhere('d.isActive = :active')
            ->andWhere('d.dateDepart > :now')
            ->setParameter('active', true)
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'with_departs' => $withDeparts,
        ];
    }

    /**
     * Recherche textuelle dans titre et description
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isActive = :active')
            ->andWhere('p.titre LIKE :term OR p.description LIKE :term')
            ->setParameter('active', true)
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('p.titre', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 