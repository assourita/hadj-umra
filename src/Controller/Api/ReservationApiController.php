<?php

namespace App\Controller\Api;

use App\Entity\Reservation;
use App\Repository\DepartRepository;
use App\Repository\ReservationRepository;
use App\Service\ReservationService;
use App\Service\PaymentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class ReservationApiController extends AbstractController
{
    public function __construct(
        private ReservationService $reservationService,
        private PaymentService $paymentService,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator
    ) {}

    /**
     * POST /api/reservations — créer une réservation
     */
    #[Route('/reservations', name: 'api_reservation_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createReservation(
        Request $request,
        DepartRepository $departRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['depart_id'], $data['type_chambre'], $data['pelerins'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $depart = $departRepository->find($data['depart_id']);
        if (!$depart) {
            return new JsonResponse(['error' => 'Départ non trouvé'], 404);
        }

        // Validation des pèlerins
        $pelerinsData = $data['pelerins'];
        if (empty($pelerinsData) || count($pelerinsData) > 4) {
            return new JsonResponse(['error' => 'Nombre de pèlerins invalide'], 400);
        }

        foreach ($pelerinsData as $index => $pelerinData) {
            if (!isset($pelerinData['nom'], $pelerinData['prenom'], $pelerinData['date_naissance'], $pelerinData['nationalite'])) {
                return new JsonResponse(['error' => "Données manquantes pour le pèlerin " . ($index + 1)], 400);
            }
        }

        try {
            $reservation = $this->reservationService->createReservation(
                $this->getUser(),
                $depart,
                $data['type_chambre'],
                $pelerinsData
            );

            return new JsonResponse([
                'success' => true,
                'reservation' => [
                    'id' => $reservation->getId(),
                    'code_dossier' => $reservation->getCodeDossier(),
                    'statut' => $reservation->getStatut(),
                    'total' => (float) $reservation->getTotal(),
                    'acompte' => (float) $reservation->getAcompte(),
                    'reste' => (float) $reservation->getReste(),
                ]
            ], 201);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * GET /api/reservations/{id} — statut & échéancier
     */
    #[Route('/reservations/{id}', name: 'api_reservation_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function showReservation(int $id, ReservationRepository $reservationRepository): JsonResponse
    {
        $reservation = $reservationRepository->find($id);

        if (!$reservation) {
            return new JsonResponse(['error' => 'Réservation non trouvée'], 404);
        }

        // Vérifier les droits d'accès
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $data = [
            'id' => $reservation->getId(),
            'code_dossier' => $reservation->getCodeDossier(),
            'statut' => $reservation->getStatut(),
            'statut_label' => $reservation->getStatutLabel(),
            'total' => (float) $reservation->getTotal(),
            'acompte' => (float) $reservation->getAcompte(),
            'reste' => (float) $reservation->getReste(),
            'total_paye' => $reservation->getTotalPaye(),
            'montant_restant' => $reservation->getMontantRestant(),
            'nb_pelerins' => $reservation->getNbPelerins(),
            'type_chambre' => $reservation->getTypeChambre(),
            'created_at' => $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
            'date_limite_document' => $reservation->getDateLimiteDocument()?->format('Y-m-d'),
            'depart' => [
                'id' => $reservation->getDepart()->getId(),
                'ville_depart' => $reservation->getDepart()->getVilleDepart(),
                'date_depart' => $reservation->getDepart()->getDateDepart()->format('Y-m-d H:i'),
                'date_retour' => $reservation->getDepart()->getDateRetour()->format('Y-m-d H:i'),
                'package' => [
                    'titre' => $reservation->getDepart()->getPackage()->getTitre(),
                    'slug' => $reservation->getDepart()->getPackage()->getSlug(),
                ]
            ],
            'pelerins' => [],
            'paiements' => [],
            'echeancier' => $this->getEcheancier($reservation)
        ];

        // Pèlerins
        foreach ($reservation->getPelerins() as $pelerin) {
            $data['pelerins'][] = [
                'id' => $pelerin->getId(),
                'nom' => $pelerin->getNom(),
                'prenom' => $pelerin->getPrenom(),
                'date_naissance' => $pelerin->getDateNaissance()->format('Y-m-d'),
                'nationalite' => $pelerin->getNationalite(),
                'sexe' => $pelerin->getSexe(),
                'documents_complets' => $pelerin->hasAllRequiredDocuments(),
            ];
        }

        // Paiements
        foreach ($reservation->getPaiements() as $paiement) {
            $data['paiements'][] = [
                'id' => $paiement->getId(),
                'mode' => $paiement->getMode(),
                'mode_label' => $paiement->getModeLabel(),
                'montant' => (float) $paiement->getMontant(),
                'statut' => $paiement->getStatut(),
                'statut_label' => $paiement->getStatutLabel(),
                'reference' => $paiement->getReference(),
                'created_at' => $paiement->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * POST /api/payments/intent — créer intent paiement (Stripe)
     */
    #[Route('/payments/intent', name: 'api_payment_intent', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function createPaymentIntent(
        Request $request,
        ReservationRepository $reservationRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['reservation_id'])) {
            return new JsonResponse(['error' => 'ID de réservation manquant'], 400);
        }

        $reservation = $reservationRepository->find($data['reservation_id']);
        if (!$reservation) {
            return new JsonResponse(['error' => 'Réservation non trouvée'], 404);
        }

        // Vérifier les droits d'accès
        if ($reservation->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        // Vérifier que la réservation peut être payée
        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_PAIEMENT) {
            return new JsonResponse(['error' => 'Cette réservation ne peut pas être payée'], 400);
        }

        $isAcompte = $data['type'] ?? 'acompte' === 'acompte';

        try {
            $paymentData = $this->paymentService->createPaymentIntent($reservation, $isAcompte);

            return new JsonResponse([
                'success' => true,
                'client_secret' => $paymentData['client_secret'],
                'payment_intent_id' => $paymentData['payment_intent_id'],
                'amount' => $isAcompte ? (float) $reservation->getAcompte() : (float) $reservation->getTotal(),
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * POST /api/payments/webhook — webhook Stripe
     */
    #[Route('/payments/webhook', name: 'api_payment_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature');
        
        // TODO: Récupérer le webhook secret depuis les paramètres
        $webhookSecret = 'whsec_your_webhook_secret';

        try {
            $success = $this->paymentService->handleWebhook($payload, $signature, $webhookSecret);
            
            return new JsonResponse(['received' => true, 'processed' => $success]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * POST /api/reservations/{id}/calculate — calculer le prix
     */
    #[Route('/reservations/calculate', name: 'api_reservation_calculate', methods: ['POST'])]
    public function calculatePrice(
        Request $request,
        DepartRepository $departRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['depart_id'], $data['type_chambre'], $data['nb_pelerins'])) {
            return new JsonResponse(['error' => 'Données manquantes'], 400);
        }

        $depart = $departRepository->find($data['depart_id']);
        if (!$depart) {
            return new JsonResponse(['error' => 'Départ non trouvé'], 404);
        }

        try {
            $priceData = $this->reservationService->calculatePrice(
                $depart,
                $data['type_chambre'],
                (int) $data['nb_pelerins']
            );

            return new JsonResponse([
                'success' => true,
                'pricing' => $priceData
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Générer l'échéancier d'une réservation
     */
    private function getEcheancier(Reservation $reservation): array
    {
        $echeances = [];

        // Acompte
        $echeances[] = [
            'type' => 'acompte',
            'label' => 'Acompte (30%)',
            'montant' => (float) $reservation->getAcompte(),
            'statut' => $reservation->getTotalPaye() >= (float) $reservation->getAcompte() ? 'paye' : 'en_attente',
            'date_limite' => $reservation->getCreatedAt()->modify('+7 days')->format('Y-m-d'),
        ];

        // Solde
        if ($reservation->getReste() && (float) $reservation->getReste() > 0) {
            $dateLimiteSolde = $reservation->getDepart()->getDateDepart()->modify('-45 days');
            
            $echeances[] = [
                'type' => 'solde',
                'label' => 'Solde',
                'montant' => (float) $reservation->getReste(),
                'statut' => $reservation->isFullyPaid() ? 'paye' : 'en_attente',
                'date_limite' => $dateLimiteSolde->format('Y-m-d'),
            ];
        }

        return $echeances;
    }
} 