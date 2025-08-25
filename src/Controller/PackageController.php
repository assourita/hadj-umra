<?php

namespace App\Controller;

use App\Entity\Package;
use App\Repository\PackageRepository;
use App\Repository\DepartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/packages')]
class PackageController extends AbstractController
{
    #[Route('/', name: 'app_packages_index')]
    public function index(
        Request $request,
        PackageRepository $packageRepository
    ): Response {
        $city = $request->query->get('city');
        $dateFrom = $request->query->get('date_from');
        $visaIncluded = $request->query->get('visa_included');

        $dateFromObject = null;
        if ($dateFrom) {
            try {
                $dateFromObject = new \DateTimeImmutable($dateFrom);
            } catch (\Exception $e) {
                $dateFromObject = null;
            }
        }

        $visaIncludedBool = null;
        if ($visaIncluded !== null) {
            $visaIncludedBool = (bool) $visaIncluded;
        }

        $packages = $packageRepository->findByCriteria($city, $dateFromObject, $visaIncludedBool);

        return $this->render('package/index.html.twig', [
            'packages' => $packages,
            'filters' => [
                'city' => $city,
                'date_from' => $dateFrom,
                'visa_included' => $visaIncluded,
            ],
        ]);
    }

    #[Route('/{slug}', name: 'app_package_show')]
    public function show(
        string $slug,
        PackageRepository $packageRepository,
        DepartRepository $departRepository
    ): Response {
        $package = $packageRepository->findBySlugWithActiveDeparts($slug);

        if (!$package) {
            throw $this->createNotFoundException('Package non trouvé');
        }

        // Départs disponibles pour ce package
        $availableDeparts = $departRepository->findByPackage($package->getId());

        return $this->render('package/show.html.twig', [
            'package' => $package,
            'available_departs' => $availableDeparts,
        ]);
    }

    #[Route('/search', name: 'app_packages_search', methods: ['GET'])]
    public function search(
        Request $request,
        PackageRepository $packageRepository
    ): Response {
        $term = $request->query->get('q', '');
        
        $packages = [];
        if (strlen($term) >= 3) {
            $packages = $packageRepository->search($term);
        }

        return $this->render('package/search.html.twig', [
            'packages' => $packages,
            'search_term' => $term,
        ]);
    }
} 