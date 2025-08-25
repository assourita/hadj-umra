<?php

namespace App\Controller\Api;

use App\Repository\PackageRepository;
use App\Repository\DepartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class PackageApiController extends AbstractController
{
    public function __construct(
        private PackageRepository $packageRepository,
        private DepartRepository $departRepository,
        private SerializerInterface $serializer
    ) {}

    /**
     * GET /api/packages — lister packages (filtres : city, dateFrom, visaIncluded)
     */
    #[Route('/packages', name: 'api_packages_list', methods: ['GET'])]
    public function listPackages(Request $request): JsonResponse
    {
        $city = $request->query->get('city');
        $dateFrom = $request->query->get('dateFrom');
        $visaIncluded = $request->query->get('visaIncluded');

        $dateFromObject = null;
        if ($dateFrom) {
            try {
                $dateFromObject = new \DateTimeImmutable($dateFrom);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Format de date invalide'], 400);
            }
        }

        $visaIncludedBool = null;
        if ($visaIncluded !== null) {
            $visaIncludedBool = filter_var($visaIncluded, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $packages = $this->packageRepository->findByCriteria($city, $dateFromObject, $visaIncludedBool);

        $data = [];
        foreach ($packages as $package) {
            $packageData = [
                'id' => $package->getId(),
                'titre' => $package->getTitre(),
                'slug' => $package->getSlug(),
                'description' => $package->getDescription(),
                'duree_jours' => $package->getDureeJours(),
                'prix_base' => $package->getPrixBase(),
                'devise' => $package->getDevise(),
                'hotel_makkah' => $package->getHotelMakkah(),
                'hotel_madinah' => $package->getHotelMadinah(),
                'images' => $package->getImages(),
                'departs_disponibles' => []
            ];

            foreach ($package->getAvailableDeparts() as $depart) {
                $packageData['departs_disponibles'][] = [
                    'id' => $depart->getId(),
                    'ville_depart' => $depart->getVilleDepart(),
                    'date_depart' => $depart->getDateDepart()->format('Y-m-d'),
                    'date_retour' => $depart->getDateRetour()->format('Y-m-d'),
                    'places_restantes' => $depart->getPlacesRestantes(),
                ];
            }

            $data[] = $packageData;
        }

        return new JsonResponse([
            'packages' => $data,
            'total' => count($data),
            'filters' => [
                'city' => $city,
                'dateFrom' => $dateFrom,
                'visaIncluded' => $visaIncluded
            ]
        ]);
    }

    /**
     * GET /api/packages/{slug} — détail package
     */
    #[Route('/packages/{slug}', name: 'api_package_show', methods: ['GET'])]
    public function showPackage(string $slug): JsonResponse
    {
        $package = $this->packageRepository->findBySlugWithActiveDeparts($slug);

        if (!$package) {
            return new JsonResponse(['error' => 'Package non trouvé'], 404);
        }

        $data = [
            'id' => $package->getId(),
            'titre' => $package->getTitre(),
            'slug' => $package->getSlug(),
            'description' => $package->getDescription(),
            'programme' => $package->getProgramme(),
            'duree_jours' => $package->getDureeJours(),
            'inclus' => $package->getInclus(),
            'non_inclus' => $package->getNonInclus(),
            'prix_base' => $package->getPrixBase(),
            'devise' => $package->getDevise(),
            'hotel_makkah' => $package->getHotelMakkah(),
            'hotel_madinah' => $package->getHotelMadinah(),
            'images' => $package->getImages(),
            'departs' => []
        ];

        foreach ($package->getDeparts() as $depart) {
            if (!$depart->isActive()) continue;

            $departData = [
                'id' => $depart->getId(),
                'ville_depart' => $depart->getVilleDepart(),
                'date_depart' => $depart->getDateDepart()->format('Y-m-d H:i'),
                'date_retour' => $depart->getDateRetour()->format('Y-m-d H:i'),
                'quota_total' => $depart->getQuotaTotal(),
                'quota_vendu' => $depart->getQuotaVendu(),
                'places_restantes' => $depart->getPlacesRestantes(),
                'compagnie_aerienne' => $depart->getCompagnieAerienne(),
                'numero_vol' => $depart->getNumeroVol(),
                'tarifs' => []
            ];

            foreach ($depart->getTarifs() as $tarif) {
                if (!$tarif->isActive()) continue;

                $departData['tarifs'][] = [
                    'id' => $tarif->getId(),
                    'type_chambre' => $tarif->getTypeChambre(),
                    'type_chambre_label' => $tarif->getTypeChambreLabel(),
                    'prix_base' => $tarif->getPrixBase(),
                    'prix_final' => $tarif->getPrixFinal(),
                    'reduction' => $tarif->getReduction(),
                    'devise' => $tarif->getDevise(),
                ];
            }

            $data['departs'][] = $departData;
        }

        return new JsonResponse($data);
    }

    /**
     * GET /api/departs/{id} — détails départ (places restantes, tarifs)
     */
    #[Route('/departs/{id}', name: 'api_depart_show', methods: ['GET'])]
    public function showDepart(int $id): JsonResponse
    {
        $depart = $this->departRepository->find($id);

        if (!$depart || !$depart->isActive()) {
            return new JsonResponse(['error' => 'Départ non trouvé'], 404);
        }

        $data = [
            'id' => $depart->getId(),
            'package' => [
                'id' => $depart->getPackage()->getId(),
                'titre' => $depart->getPackage()->getTitre(),
                'slug' => $depart->getPackage()->getSlug(),
            ],
            'ville_depart' => $depart->getVilleDepart(),
            'date_depart' => $depart->getDateDepart()->format('Y-m-d H:i'),
            'date_retour' => $depart->getDateRetour()->format('Y-m-d H:i'),
            'quota_total' => $depart->getQuotaTotal(),
            'quota_vendu' => $depart->getQuotaVendu(),
            'places_restantes' => $depart->getPlacesRestantes(),
            'compagnie_aerienne' => $depart->getCompagnieAerienne(),
            'numero_vol' => $depart->getNumeroVol(),
            'remarques' => $depart->getRemarques(),
            'tarifs' => []
        ];

        foreach ($depart->getTarifs() as $tarif) {
            if (!$tarif->isActive()) continue;

            $data['tarifs'][] = [
                'id' => $tarif->getId(),
                'type_chambre' => $tarif->getTypeChambre(),
                'type_chambre_label' => $tarif->getTypeChambreLabel(),
                'prix_base' => (float) $tarif->getPrixBase(),
                'prix_final' => $tarif->getPrixFinal(),
                'reduction' => $tarif->getReduction() ? (float) $tarif->getReduction() : null,
                'devise' => $tarif->getDevise(),
                'formatted_price' => $tarif->getFormattedPrice(),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * GET /api/packages/search — recherche packages
     */
    #[Route('/packages/search', name: 'api_packages_search', methods: ['GET'])]
    public function searchPackages(Request $request): JsonResponse
    {
        $term = $request->query->get('q', '');
        
        if (strlen($term) < 3) {
            return new JsonResponse(['error' => 'Le terme de recherche doit faire au moins 3 caractères'], 400);
        }

        $packages = $this->packageRepository->search($term);

        $data = [];
        foreach ($packages as $package) {
            $data[] = [
                'id' => $package->getId(),
                'titre' => $package->getTitre(),
                'slug' => $package->getSlug(),
                'description' => substr($package->getDescription(), 0, 200) . '...',
                'duree_jours' => $package->getDureeJours(),
                'prix_base' => $package->getPrixBase(),
                'devise' => $package->getDevise(),
                'departs_count' => $package->getAvailableDeparts()->count(),
            ];
        }

        return new JsonResponse([
            'packages' => $data,
            'total' => count($data),
            'search_term' => $term
        ]);
    }
} 