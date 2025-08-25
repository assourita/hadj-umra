<?php

namespace App\Controller\Admin;

use App\Repository\ReservationRepository;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_admin_dashboard')]
    public function index(
        ReservationRepository $reservationRepository,
        PackageRepository $packageRepository,
        UserRepository $userRepository
    ): Response {
        // Statistiques principales
        $reservationStats = $reservationRepository->getReservationStats();
        $packageStats = $packageRepository->getPackageStats();
        
        // Réservations nécessitant une action
        $reservationsRequiringAction = $reservationRepository->findRequiringAction();
        
        // Utilisateurs récents
        $recentUsers = $userRepository->findRecentUsers(7);

        // Données pour les graphiques
        $monthlyStats = $this->getMonthlyStats($reservationRepository);

        // Nouvelles statistiques pour le dashboard moderne
        $stats = [
            'total_reservations' => $reservationRepository->count([]),
            'en_attente_approbation' => $reservationRepository->count(['statut' => 'en_attente_approbation']),
            'en_attente_documents' => $reservationRepository->count(['statut' => 'en_attente_documents']),
            'total_users' => $userRepository->count([]),
            'total_packages' => $packageRepository->count(['isActive' => true]),
            'messages_non_lus' => 0, // À implémenter avec ContactMessageRepository
        ];

        return $this->render('admin/dashboard/index.html.twig', [
            'reservation_stats' => $reservationStats,
            'package_stats' => $packageStats,
            'reservations_requiring_action' => $reservationsRequiringAction,
            'recent_users' => $recentUsers,
            'monthly_stats' => $monthlyStats,
            'stats' => $stats,
        ]);
    }

    private function getMonthlyStats(ReservationRepository $reservationRepository): array
    {
        $stats = [];
        
        // Statistiques des 6 derniers mois
        for ($i = 5; $i >= 0; $i--) {
            $date = new \DateTimeImmutable("first day of -{$i} months");
            $nextMonth = $date->modify('first day of next month');
            
            $count = $reservationRepository->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->andWhere('r.createdAt >= :start AND r.createdAt < :end')
                ->setParameter('start', $date)
                ->setParameter('end', $nextMonth)
                ->getQuery()
                ->getSingleScalarResult();

            $ca = $reservationRepository->createQueryBuilder('r')
                ->select('SUM(r.total)')
                ->andWhere('r.createdAt >= :start AND r.createdAt < :end')
                ->andWhere('r.statut IN (:statuts)')
                ->setParameter('start', $date)
                ->setParameter('end', $nextMonth)
                ->setParameter('statuts', ['confirme', 'complet', 'acompte_paye'])
                ->getQuery()
                ->getSingleScalarResult() ?? 0;

            $stats[] = [
                'month' => $date->format('M Y'),
                'reservations' => $count,
                'ca' => $ca,
            ];
        }

        return $stats;
    }
} 