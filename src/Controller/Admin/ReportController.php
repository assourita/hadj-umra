<?php

namespace App\Controller\Admin;

use App\Repository\ReservationRepository;
use App\Repository\PackageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reports')]
#[IsGranted('ROLE_ADMIN')]
class ReportController extends AbstractController
{
    #[Route('/', name: 'app_admin_reports_index')]
    public function index(
        ReservationRepository $reservationRepository,
        PackageRepository $packageRepository,
        UserRepository $userRepository
    ): Response {
        // Statistiques générales
        $stats = [
            'total_reservations' => $reservationRepository->count([]),
            'reservations_confirmees' => $reservationRepository->count(['statut' => 'confirme']),
            'reservations_en_attente' => $reservationRepository->count(['statut' => 'en_attente_paiement']),
            'total_packages' => $packageRepository->count([]),
            'total_users' => $userRepository->count([]),
        ];

        // Statistiques par mois (6 derniers mois)
        $monthlyStats = [];
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

            $monthlyStats[] = [
                'month' => $date->format('M Y'),
                'reservations' => $count,
                'ca' => $ca,
            ];
        }

        // Top packages
        $topPackages = $packageRepository->createQueryBuilder('p')
            ->select('p.titre, COUNT(r.id) as reservation_count')
            ->leftJoin('p.departs', 'd')
            ->leftJoin('d.reservations', 'r')
            ->groupBy('p.id')
            ->orderBy('reservation_count', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('admin/reports/index.html.twig', [
            'stats' => $stats,
            'monthly_stats' => $monthlyStats,
            'top_packages' => $topPackages,
        ]);
    }

    #[Route('/reservations', name: 'app_admin_reports_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAll();

        return $this->render('admin/reports/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/financial', name: 'app_admin_reports_financial')]
    public function financial(ReservationRepository $reservationRepository): Response
    {
        // Statistiques financières - utiliser des requêtes séparées plus simples
        $caConfirme = $reservationRepository->createQueryBuilder('r')
            ->select('SUM(r.total)')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'confirme')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $caEnAttente = $reservationRepository->createQueryBuilder('r')
            ->select('SUM(r.total)')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'en_attente_paiement')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $caComplet = $reservationRepository->createQueryBuilder('r')
            ->select('SUM(r.total)')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'complet')
            ->getQuery()
            ->getSingleScalarResult() ?? 0;

        $countConfirme = $reservationRepository->count(['statut' => 'confirme']);
        $countEnAttente = $reservationRepository->count(['statut' => 'en_attente_paiement']);
        $countComplet = $reservationRepository->count(['statut' => 'complet']);

        $financialStats = [
            'ca_confirme' => $caConfirme,
            'ca_en_attente' => $caEnAttente,
            'ca_complet' => $caComplet,
            'count_confirme' => $countConfirme,
            'count_en_attente' => $countEnAttente,
            'count_complet' => $countComplet,
        ];

        return $this->render('admin/reports/financial.html.twig', [
            'financial_stats' => $financialStats,
        ]);
    }
} 