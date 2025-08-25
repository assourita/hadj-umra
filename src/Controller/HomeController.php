<?php

namespace App\Controller;

use App\Repository\PackageRepository;
use App\Repository\DepartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PackageRepository $packageRepository,
        DepartRepository $departRepository
    ): Response {
        // Packages populaires pour la page d'accueil
        $popularPackages = $packageRepository->findPopularPackages(6);
        
        // Départs prochains
        $upcomingDeparts = $departRepository->findUpcomingDeparts();
        
        // Packages récents
        $recentPackages = $packageRepository->findRecentPackages(3);

        return $this->render('home/index.html.twig', [
            'packages' => $popularPackages,
            'upcoming_departs' => $upcomingDeparts,
            'recent_packages' => $recentPackages,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }
} 