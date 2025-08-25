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
}
