<?php

namespace App\Repository;

use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    /**
     * Réservations d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->addSelect('d', 'p')
            ->andWhere('r.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Toutes les réservations triées par date de création
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->addSelect('u', 'd', 'p')
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations par statut
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->addSelect('u', 'd', 'p')
            ->andWhere('r.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations en attente de paiement depuis plus de X jours
     */
    public function findPendingPayments(int $days = 3): array
    {
        $date = new \DateTimeImmutable(sprintf('-%d days', $days));
        
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->andWhere('r.statut = :statut')
            ->andWhere('r.createdAt <= :date')
            ->setParameter('statut', Reservation::STATUT_EN_ATTENTE_PAIEMENT)
            ->setParameter('date', $date)
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Réservations par départ
     */
    public function findByDepart(int $departId): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.pelerins', 'pel')
            ->addSelect('u', 'pel')
            ->andWhere('r.depart = :departId')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('departId', $departId)
            ->setParameter('statuts', [
                Reservation::STATUT_CONFIRME,
                Reservation::STATUT_COMPLET,
                Reservation::STATUT_ACOMPTE_PAYE
            ])
            ->orderBy('r.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques des réservations
     */
    public function getReservationStats(): array
    {
        $qb = $this->createQueryBuilder('r');
        
        // Total des réservations
        $total = $qb->select('COUNT(r.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // Réservations confirmées
        $confirmees = $this->createQueryBuilder('r')
                ->select('COUNT(r.id)')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('statuts', [
                Reservation::STATUT_CONFIRME,
                Reservation::STATUT_COMPLET,
                Reservation::STATUT_ACOMPTE_PAYE
            ])
                ->getQuery()
                ->getSingleScalarResult();

        // Chiffre d'affaires total
        $caTotal = $this->createQueryBuilder('r')
            ->select('SUM(r.total)')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('statuts', [
                Reservation::STATUT_CONFIRME,
                Reservation::STATUT_COMPLET,
                Reservation::STATUT_ACOMPTE_PAYE
            ])
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        return [
            'total' => $total,
            'confirmees' => $confirmees,
            'ca_total' => $caTotal,
        ];
    }

    /**
     * Réservations nécessitant une action
     */
    public function findRequiringAction(): array
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->addSelect('u', 'd', 'p')
            ->andWhere('r.statut IN (:statuts)')
            ->setParameter('statuts', [
                Reservation::STATUT_BROUILLON,
                Reservation::STATUT_EN_ATTENTE_PAIEMENT
            ])
            ->orderBy('r.createdAt', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par code dossier
     */
    public function findByCodeDossier(string $codeDossier): ?Reservation
    {
        return $this->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->leftJoin('r.pelerins', 'pel')
            ->leftJoin('r.paiements', 'pai')
            ->addSelect('u', 'd', 'p', 'pel', 'pai')
            ->andWhere('r.codeDossier = :code')
            ->setParameter('code', $codeDossier)
            ->getQuery()
            ->getOneOrNullResult();
    }

} 