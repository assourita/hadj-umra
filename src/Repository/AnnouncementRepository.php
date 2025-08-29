<?php

namespace App\Repository;

use App\Entity\Announcement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Announcement>
 *
 * @method Announcement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Announcement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Announcement[]    findAll()
 * @method Announcement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnouncementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Announcement::class);
    }

    public function save(Announcement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Announcement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Announcement[] Returns an array of published Announcement objects
     */
    public function findPublished(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->setParameter('val', true)
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of published Announcement objects by type
     */
    public function findPublishedByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->andWhere('a.type = :type')
            ->setParameter('val', true)
            ->setParameter('type', $type)
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of recent Announcement objects
     */
    public function findRecent(int $limit = 5): array
    {
        return $this->createQueryBuilder('a')
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of pending Announcement objects
     */
    public function findPending(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->andWhere('a.publishedAt IS NULL')
            ->setParameter('val', false)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of Announcement objects by status
     */
    public function findByStatus(bool $isPublished): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->setParameter('val', $isPublished)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of Announcement objects by type
     */
    public function findByType(string $type): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.type = :type')
            ->setParameter('type', $type)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of high priority Announcement objects
     */
    public function findHighPriority(): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->andWhere('a.priority >= :priority')
            ->setParameter('val', true)
            ->setParameter('priority', 5)
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Announcement[] Returns an array of monthly Announcement objects
     */
    public function findMonthly(): array
    {
        $startOfMonth = new \DateTime('first day of this month');
        $endOfMonth = new \DateTime('last day of this month');

        return $this->createQueryBuilder('a')
            ->andWhere('a.isPublished = :val')
            ->andWhere('a.publishedAt IS NOT NULL')
            ->andWhere('a.publishedAt BETWEEN :start AND :end')
            ->setParameter('val', true)
            ->setParameter('start', $startOfMonth)
            ->setParameter('end', $endOfMonth)
            ->orderBy('a.priority', 'DESC')
            ->addOrderBy('a.publishedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
