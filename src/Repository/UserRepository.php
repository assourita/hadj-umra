<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Recherche des utilisateurs par rôle
     */
    public function findByRole(string $role): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', json_encode($role))
            ->orderBy('u.nom', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des clients (utilisateurs avec ROLE_USER uniquement)
     */
    public function findClients(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles = :roles OR u.roles IS NULL')
            ->setParameter('roles', json_encode(['ROLE_USER']))
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des administrateurs
     */
    public function findAdmins(): array
    {
        return $this->findByRole('ROLE_ADMIN');
    }

    /**
     * Statistiques des utilisateurs par pays
     */
    public function getStatsByCountry(): array
    {
        return $this->createQueryBuilder('u')
            ->select('u.pays, COUNT(u.id) as total')
            ->groupBy('u.pays')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Utilisateurs récemment inscrits
     */
    public function findRecentUsers(int $days = 7): array
    {
        $date = new \DateTimeImmutable(sprintf('-%d days', $days));
        
        return $this->createQueryBuilder('u')
            ->andWhere('u.createdAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche par nom/prénom/email
     */
    public function search(string $term): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.nom LIKE :term OR u.prenom LIKE :term OR u.email LIKE :term')
            ->setParameter('term', '%' . $term . '%')
            ->orderBy('u.nom', 'ASC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();
    }
}
