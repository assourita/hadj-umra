<?php

namespace App\Service;

use App\Entity\Reservation;
use App\Entity\Paiement;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class PaymentService
{
    private StripeClient $stripe;

    public function __construct(
        private EntityManagerInterface $em,
        private ReservationService $reservationService,
        string $stripeSecretKey
    ) {
        $this->stripe = new StripeClient($stripeSecretKey);
    }

    /**
     * Créer un Payment Intent Stripe
     */
    public function createPaymentIntent(Reservation $reservation, bool $isAcompte = true): array
    {
        $montant = $isAcompte ? (float) $reservation->getAcompte() : (float) $reservation->getTotal();
        
        // Stripe attend le montant en centimes
        $montantCentimes = (int) ($montant * 100);

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $montantCentimes,
                'currency' => 'eur', // Convertir selon la devise
                'metadata' => [
                    'reservation_id' => $reservation->getId(),
                    'code_dossier' => $reservation->getCodeDossier(),
                    'type_paiement' => $isAcompte ? 'acompte' : 'total',
                ],
                'description' => sprintf(
                    '%s pour la réservation %s',
                    $isAcompte ? 'Acompte' : 'Paiement total',
                    $reservation->getCodeDossier()
                ),
            ]);

            // Créer l'entité Paiement
            $paiement = new Paiement();
            $paiement->setReservation($reservation);
            $paiement->setMode(Paiement::MODE_CARTE);
            $paiement->setMontant((string) $montant);
            $paiement->setReference($paymentIntent->id);
            $paiement->setStripePaymentIntentId($paymentIntent->id);
            $paiement->setDevise('XOF'); // Devise originale
            $paiement->setStatut(Paiement::STATUT_EN_ATTENTE);

            $this->em->persist($paiement);
            $this->em->flush();

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'paiement_id' => $paiement->getId(),
            ];

        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la création du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Confirmer un paiement Stripe
     */
    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            
            if ($paymentIntent->status === 'succeeded') {
                // Trouver le paiement correspondant
                $paiement = $this->em->getRepository(Paiement::class)
                    ->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);

                if ($paiement) {
                    $paiement->setStatut(Paiement::STATUT_VALIDE);
                    $paiement->setUpdatedAt(new \DateTimeImmutable());

                    // Mettre à jour le statut de la réservation
                    $reservation = $paiement->getReservation();
                    
                    if ($reservation->isFullyPaid()) {
                        $reservation->setStatut(Reservation::STATUT_COMPLET);
                    } else {
                        $reservation->setStatut(Reservation::STATUT_ACOMPTE_PAYE);
                    }

                    $this->em->flush();

                    // Confirmer la réservation
                    $this->reservationService->confirmReservation($reservation);

                    return true;
                }
            }

            return false;

        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la confirmation du paiement: ' . $e->getMessage());
        }
    }

    /**
     * Traiter le webhook Stripe
     */
    public function handleWebhook(string $payload, string $signature, string $webhookSecret): bool
    {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $webhookSecret);

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    return $this->confirmPayment($paymentIntent->id);

                case 'payment_intent.payment_failed':
                    $paymentIntent = $event->data->object;
                    return $this->handlePaymentFailure($paymentIntent->id);

                default:
                    // Événement non géré
                    return true;
            }

        } catch (\Exception $e) {
            // Log l'erreur
            return false;
        }
    }

    /**
     * Gérer l'échec d'un paiement
     */
    private function handlePaymentFailure(string $paymentIntentId): bool
    {
        $paiement = $this->em->getRepository(Paiement::class)
            ->findOneBy(['stripePaymentIntentId' => $paymentIntentId]);

        if ($paiement) {
            $paiement->setStatut(Paiement::STATUT_REFUSE);
            $paiement->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();

            return true;
        }

        return false;
    }

    /**
     * Créer un paiement manuel (espèces, virement)
     */
    public function createManualPayment(
        Reservation $reservation,
        string $mode,
        float $montant,
        ?string $reference = null
    ): Paiement {
        $paiement = new Paiement();
        $paiement->setReservation($reservation);
        $paiement->setMode($mode);
        $paiement->setMontant((string) $montant);
        $paiement->setReference($reference);
        $paiement->setDevise('XOF');
        $paiement->setStatut(Paiement::STATUT_EN_ATTENTE);

        $this->em->persist($paiement);
        $this->em->flush();

        return $paiement;
    }

    /**
     * Convertir une devise (simpliste pour le MVP)
     */
    public function convertCurrency(float $amount, string $from, string $to): float
    {
        // Taux de change fixes pour le MVP
        $rates = [
            'XOF' => ['EUR' => 0.00152, 'USD' => 0.00164, 'SAR' => 0.00616],
            'EUR' => ['XOF' => 656.98, 'USD' => 1.08, 'SAR' => 4.05],
            'USD' => ['XOF' => 609.26, 'EUR' => 0.93, 'SAR' => 3.75],
            'SAR' => ['XOF' => 162.47, 'EUR' => 0.25, 'USD' => 0.27],
        ];

        if ($from === $to) {
            return $amount;
        }

        if (isset($rates[$from][$to])) {
            return $amount * $rates[$from][$to];
        }

        // Si pas de taux direct, passer par EUR
        if ($from !== 'EUR' && $to !== 'EUR') {
            $amountEur = $amount * ($rates[$from]['EUR'] ?? 1);
            return $amountEur * ($rates['EUR'][$to] ?? 1);
        }

        return $amount; // Fallback
    }

    /**
     * Formater un montant selon la devise
     */
    public function formatAmount(float $amount, string $currency): string
    {
        $symbols = [
            'XOF' => 'FCFA',
            'EUR' => '€',
            'USD' => '$',
            'SAR' => 'SAR',
        ];

        $symbol = $symbols[$currency] ?? $currency;
        
        if ($currency === 'XOF') {
            return number_format($amount, 0, ',', ' ') . ' ' . $symbol;
        }

        return number_format($amount, 2, ',', ' ') . ' ' . $symbol;
    }
} 