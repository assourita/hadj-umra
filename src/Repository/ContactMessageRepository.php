<?php

namespace App\Repository;

use App\Entity\ContactMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactMessage>
 */
class ContactMessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactMessage::class);
    }

    /**
     * @return ContactMessage[]
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('cm')
            ->orderBy('cm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ContactMessage[]
     */
    public function findUnreplied(): array
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.reponse IS NULL')
            ->orderBy('cm.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return ContactMessage[]
     */
    public function findByStatut(string $statut): array
    {
        return $this->createQueryBuilder('cm')
            ->where('cm.statut = :statut')
            ->setParameter('statut', $statut)
            ->orderBy('cm.createdAt', 'DESC')
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

    /**
     * Recherche les messages par email (insensible à la casse)
     */
    public function findByEmailInsensitive(string $email): array
    {
        return $this->createQueryBuilder('m')
            ->where('LOWER(m.email) = LOWER(:email)')
            ->setParameter('email', trim($email))
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche tous les messages (pour debug)
     */
    public function findAllWithEmail(): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.email, m.nom, m.sujet, m.createdAt')
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
