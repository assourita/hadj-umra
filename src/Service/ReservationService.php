<?php

namespace App\Service;

use App\Entity\Depart;
use App\Entity\Reservation;
use App\Entity\Pelerin;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class ReservationService
{
    public function __construct(
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private string $defaultCurrency = 'XOF',
        private float $acomptePercentage = 30.0
    ) {}

    /**
     * Créer une nouvelle réservation
     */
    public function createReservation(
        User $user,
        Depart $depart,
        string $typeChambre,
        array $pelerinsData
    ): Reservation {
        // Vérifier la disponibilité
        if (!$depart->canAccommodate(count($pelerinsData))) {
            throw new \Exception('Pas assez de places disponibles');
        }

        // Calculer le prix total
        $tarif = $depart->getTarifForChambre($typeChambre);
        if (!$tarif) {
            throw new \Exception('Tarif non trouvé pour ce type de chambre');
        }

        $prixTotal = $tarif->getPrixFinal() * count($pelerinsData);

        try {
            $this->em->beginTransaction();

            // Créer la réservation
            $reservation = new Reservation();
            $reservation->setUser($user);
            $reservation->setDepart($depart);
            $reservation->setTypeChambre($typeChambre);
            $reservation->setNbPelerins(count($pelerinsData));
            $reservation->setTotal((string) $prixTotal);
            $reservation->calculateAcompte($this->acomptePercentage);

            $this->em->persist($reservation);

            // Créer les pèlerins
            foreach ($pelerinsData as $pelerinData) {
                $pelerin = new Pelerin();
                $pelerin->setReservation($reservation);
                $pelerin->setNom($pelerinData['nom']);
                $pelerin->setPrenom($pelerinData['prenom']);
                $pelerin->setDateNaissance(new \DateTimeImmutable($pelerinData['date_naissance']));
                $pelerin->setNationalite($pelerinData['nationalite']);
                $pelerin->setSexe($pelerinData['sexe']);

                $this->em->persist($pelerin);
                $reservation->addPelerin($pelerin);
            }

            // Mettre à jour le quota
            $depart->setQuotaVendu($depart->getQuotaVendu() + count($pelerinsData));

            $this->em->flush();
            $this->em->commit();

            // Envoyer email de confirmation
            $this->sendReservationConfirmationEmail($reservation);

            return $reservation;

        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Confirmer une réservation après paiement
     */
    public function confirmReservation(Reservation $reservation): void
    {
        $reservation->setStatut(Reservation::STATUT_CONFIRME);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        
        // Définir la date limite pour les documents (30 jours avant le départ)
        $dateLimite = $reservation->getDepart()->getDateDepart()->modify('-30 days');
        $reservation->setDateLimiteDocument($dateLimite);

        $this->em->flush();

        // Envoyer email de confirmation
        $this->sendPaymentConfirmationEmail($reservation);
    }

    /**
     * Annuler une réservation
     */
    public function cancelReservation(Reservation $reservation, string $raison = null): void
    {
        if (!$reservation->canBeCancelled()) {
            throw new \Exception('Cette réservation ne peut plus être annulée');
        }

        // Libérer les places
        $depart = $reservation->getDepart();
        $depart->setQuotaVendu($depart->getQuotaVendu() - $reservation->getNbPelerins());

        $reservation->setStatut(Reservation::STATUT_ANNULE);
        if ($raison) {
            $reservation->setRemarques($raison);
        }

        $this->em->flush();

        // Envoyer email d'annulation
        $this->sendCancellationEmail($reservation);
    }

    /**
     * Calculer le prix d'une réservation
     */
    public function calculatePrice(Depart $depart, string $typeChambre, int $nbPelerins): array
    {
        $tarif = $depart->getTarifForChambre($typeChambre);
        if (!$tarif) {
            throw new \Exception('Tarif non trouvé');
        }

        $prixUnitaire = $tarif->getPrixFinal();
        $prixTotal = $prixUnitaire * $nbPelerins;
        $acompte = $prixTotal * ($this->acomptePercentage / 100);
        $reste = $prixTotal - $acompte;

        return [
            'prix_unitaire' => $prixUnitaire,
            'prix_total' => $prixTotal,
            'acompte' => $acompte,
            'reste' => $reste,
            'devise' => $tarif->getDevise(),
        ];
    }

    /**
     * Vérifier si une réservation peut être modifiée
     */
    public function canModifyReservation(Reservation $reservation): bool
    {
        return in_array($reservation->getStatut(), [
            Reservation::STATUT_BROUILLON,
            Reservation::STATUT_EN_ATTENTE_PAIEMENT
        ]) && $reservation->getDepart()->getDateDepart() > new \DateTimeImmutable('+7 days');
    }

    /**
     * Envoyer email de confirmation de réservation
     */
    private function sendReservationConfirmationEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@omra-himra.com')
            ->to($reservation->getUser()->getEmail())
            ->subject('Confirmation de votre réservation Omra - ' . $reservation->getCodeDossier())
            ->html($this->getReservationEmailTemplate($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas faire échouer la réservation
        }
    }

    /**
     * Envoyer email de confirmation de paiement
     */
    private function sendPaymentConfirmationEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@omra-himra.com')
            ->to($reservation->getUser()->getEmail())
            ->subject('Paiement confirmé - ' . $reservation->getCodeDossier())
            ->html($this->getPaymentConfirmationEmailTemplate($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur
        }
    }

    /**
     * Envoyer email d'annulation
     */
    private function sendCancellationEmail(Reservation $reservation): void
    {
        $email = (new Email())
            ->from('noreply@omra-himra.com')
            ->to($reservation->getUser()->getEmail())
            ->subject('Annulation de votre réservation - ' . $reservation->getCodeDossier())
            ->html($this->getCancellationEmailTemplate($reservation));

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log l'erreur
        }
    }

    private function getReservationEmailTemplate(Reservation $reservation): string
    {
        return sprintf('
            <h2>Confirmation de réservation</h2>
            <p>Bonjour %s,</p>
            <p>Votre réservation a été créée avec succès !</p>
            <p><strong>Code dossier :</strong> %s</p>
            <p><strong>Package :</strong> %s</p>
            <p><strong>Départ :</strong> %s le %s</p>
            <p><strong>Nombre de pèlerins :</strong> %d</p>
            <p><strong>Montant total :</strong> %s</p>
            <p><strong>Acompte à régler :</strong> %s</p>
            <p>Merci de procéder au paiement pour confirmer votre réservation.</p>
            <p>Cordialement,<br>L\'équipe Omra Himra</p>
        ',
            $reservation->getUser()->getFullName(),
            $reservation->getCodeDossier(),
            $reservation->getDepart()->getPackage()->getTitre(),
            $reservation->getDepart()->getVilleDepart(),
            $reservation->getDepart()->getDateDepart()->format('d/m/Y'),
            $reservation->getNbPelerins(),
            number_format((float) $reservation->getTotal(), 0, ',', ' ') . ' XOF',
            number_format((float) $reservation->getAcompte(), 0, ',', ' ') . ' XOF'
        );
    }

    private function getPaymentConfirmationEmailTemplate(Reservation $reservation): string
    {
        return sprintf('
            <h2>Paiement confirmé</h2>
            <p>Bonjour %s,</p>
            <p>Votre paiement a été confirmé avec succès !</p>
            <p><strong>Code dossier :</strong> %s</p>
            <p><strong>Montant payé :</strong> %s XOF</p>
            <p>Votre réservation est maintenant confirmée. Vous recevrez prochainement les instructions pour l\'envoi de vos documents.</p>
            <p>Cordialement,<br>L\'équipe Omra Himra</p>
        ',
            $reservation->getUser()->getFullName(),
            $reservation->getCodeDossier(),
            number_format($reservation->getTotalPaye(), 0, ',', ' ')
        );
    }

    private function getCancellationEmailTemplate(Reservation $reservation): string
    {
        return sprintf('
            <h2>Annulation de réservation</h2>
            <p>Bonjour %s,</p>
            <p>Votre réservation %s a été annulée.</p>
            <p>Si vous avez effectué un paiement, le remboursement sera traité dans les plus brefs délais.</p>
            <p>Pour toute question, n\'hésitez pas à nous contacter.</p>
            <p>Cordialement,<br>L\'équipe Omra Himra</p>
        ',
            $reservation->getUser()->getFullName(),
            $reservation->getCodeDossier()
        );
    }
} 