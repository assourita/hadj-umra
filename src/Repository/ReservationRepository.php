<?php

namespace App\Repository;

use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 *
 * @method Reservation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reservation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reservation[]    findAll()
 * @method Reservation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function save(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Reservation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Reservation[] Returns an array of Reservation objects
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :val')
            ->setParameter('val', $status)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Reservation[] Returns an array of pending reservations
     */
    public function findPendingReservations(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Reservation[] Returns an array of paid reservations
     */
    public function findPaidReservations(): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.paymentStatus = :status')
            ->setParameter('status', 'completed')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Reservation[] Returns an array of reservations by package
     */
    public function findByPackage(int $packageId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.package = :packageId')
            ->setParameter('packageId', $packageId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Reservation[] Returns an array of recent reservations
     */
    public function findRecentReservations(int $limit = 10): array
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics(): array
    {
        $qb = $this->createQueryBuilder('r');
        
        $total = $qb->select('COUNT(r.id)')->getQuery()->getSingleScalarResult();
        
        $pending = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
            
        $paid = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->where('r.paymentStatus = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();
            
        $totalRevenue = $this->createQueryBuilder('r')
            ->select('SUM(r.totalPrice)')
            ->where('r.paymentStatus = :status')
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();
            
        return [
            'total' => $total,
            'pending' => $pending,
            'paid' => $paid,
            'totalRevenue' => $totalRevenue ?? 0
        ];
    }
}
