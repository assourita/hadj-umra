<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('/form/{reservationId}', name: 'app_payment_form', methods: ['GET'])]
    public function paymentForm(ReservationRepository $reservationRepository, int $reservationId): Response
    {
        $reservation = $reservationRepository->find($reservationId);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('payment/form.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/process/{reservationId}', name: 'app_payment_process', methods: ['POST'])]
    public function processPayment(Request $request, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager, int $reservationId): Response
    {
        $reservation = $reservationRepository->find($reservationId);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        $paymentMethod = $request->request->get('payment_method');
        
        if (!in_array($paymentMethod, ['orange_money', 'card'])) {
            $this->addFlash('error', 'Méthode de paiement invalide');
            return $this->redirectToRoute('app_payment_form', ['reservationId' => $reservationId]);
        }

        $reservation->setPaymentMethod($paymentMethod);
        
        // Simuler le processus de paiement
        // Dans un vrai projet, vous intégreriez ici les APIs Orange Money et cartes bancaires
        
        if ($paymentMethod === 'orange_money') {
            // Simulation API Orange Money
            $transactionId = 'OM_' . time() . '_' . $reservationId;
            $success = $this->simulateOrangeMoneyPayment($request);
        } else {
            // Simulation API Carte Bancaire
            $transactionId = 'CB_' . time() . '_' . $reservationId;
            $success = $this->simulateCardPayment($request);
        }

        if ($success) {
            $reservation->setPaymentStatus('completed');
            $reservation->setStatus('confirmed');
            $reservation->setTransactionId($transactionId);
            
            $entityManager->flush();
            
            $this->addFlash('success', 'Paiement effectué avec succès !');
            
            // Envoyer email de confirmation
            $this->sendConfirmationEmail($reservation);
            
            return $this->redirectToRoute('app_payment_success', ['reservationId' => $reservationId]);
        } else {
            $reservation->setPaymentStatus('failed');
            $entityManager->flush();
            
            $this->addFlash('error', 'Échec du paiement. Veuillez réessayer.');
            return $this->redirectToRoute('app_payment_form', ['reservationId' => $reservationId]);
        }
    }

    #[Route('/success/{reservationId}', name: 'app_payment_success', methods: ['GET'])]
    public function paymentSuccess(ReservationRepository $reservationRepository, int $reservationId): Response
    {
        $reservation = $reservationRepository->find($reservationId);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Réservation non trouvée');
        }

        return $this->render('payment/success.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/callback/orange-money', name: 'app_payment_orange_callback', methods: ['POST'])]
    public function orangeMoneyCallback(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Traitement du callback Orange Money
        $data = json_decode($request->getContent(), true);
        
        // Vérifier la signature et traiter la réponse
        // Ceci est un exemple simplifié
        
        $transactionId = $data['transaction_id'] ?? null;
        $status = $data['status'] ?? 'failed';
        
        if ($transactionId) {
            // Mettre à jour la réservation
            // Logique de mise à jour...
        }
        
        return new Response('OK', 200);
    }

    #[Route('/callback/card', name: 'app_payment_card_callback', methods: ['POST'])]
    public function cardCallback(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Traitement du callback Carte Bancaire
        $data = json_decode($request->getContent(), true);
        
        // Vérifier la signature et traiter la réponse
        // Ceci est un exemple simplifié
        
        $transactionId = $data['transaction_id'] ?? null;
        $status = $data['status'] ?? 'failed';
        
        if ($transactionId) {
            // Mettre à jour la réservation
            // Logique de mise à jour...
        }
        
        return new Response('OK', 200);
    }

    private function simulateOrangeMoneyPayment(Request $request): bool
    {
        // Simulation du paiement Orange Money
        // Dans un vrai projet, vous feriez un appel API vers Orange Money
        
        $phoneNumber = $request->request->get('orange_money_phone');
        $amount = $request->request->get('amount');
        
        // Vérifications basiques
        if (empty($phoneNumber) || empty($amount)) {
            return false;
        }
        
        // Simulation de succès (90% de chance)
        return rand(1, 10) <= 9;
    }

    private function simulateCardPayment(Request $request): bool
    {
        // Simulation du paiement par carte bancaire
        // Dans un vrai projet, vous feriez un appel API vers votre processeur de paiement
        
        $cardNumber = $request->request->get('card_number');
        $expiryDate = $request->request->get('expiry_date');
        $cvv = $request->request->get('cvv');
        
        // Vérifications basiques
        if (empty($cardNumber) || empty($expiryDate) || empty($cvv)) {
            return false;
        }
        
        // Simulation de succès (95% de chance)
        return rand(1, 20) <= 19;
    }

    private function sendConfirmationEmail(Reservation $reservation): void
    {
        // Envoi d'email de confirmation
        // Dans un vrai projet, vous utiliseriez le service Mailer de Symfony
        
        $to = $reservation->getEmail();
        $subject = 'Confirmation de votre réservation - DƐMƐ Travel';
        $message = sprintf(
            "Bonjour %s,\n\n" .
            "Votre réservation pour le package '%s' a été confirmée.\n" .
            "Montant payé : %s€\n" .
            "Numéro de transaction : %s\n\n" .
            "Nous vous contacterons bientôt pour les détails de votre voyage.\n\n" .
            "Cordialement,\nL'équipe DƐMƐ Travel",
            $reservation->getFullName(),
            $reservation->getPackage()->getName(),
            $reservation->getTotalPrice(),
            $reservation->getTransactionId()
        );
        
        // mail($to, $subject, $message);
        
        // Pour l'instant, on log juste
        error_log("Email de confirmation envoyé à : $to");
    }
}
