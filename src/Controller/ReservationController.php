<?php

namespace App\Controller;

use App\Entity\Depart;
use App\Entity\Reservation;
use App\Entity\Pelerin;
use App\Entity\Paiement;
use App\Repository\DepartRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/reservation')]
#[IsGranted('ROLE_USER')]
class ReservationController extends AbstractController
{
    #[Route('/step1/{departId}', name: 'app_reservation_step1')]
    public function step1(
        int $departId,
        DepartRepository $departRepository,
        SessionInterface $session
    ): Response {
        $depart = $departRepository->find($departId);
        
        if (!$depart || !$depart->isActive() || !$depart->hasAvailableSpots()) {
            $this->addFlash('error', 'Ce départ n\'est plus disponible.');
            return $this->redirectToRoute('app_packages_index');
        }

        // Stocker les informations en session
        $session->set('reservation_data', [
            'depart_id' => $departId,
            'step' => 1
        ]);

        return $this->render('reservation/step1.html.twig', [
            'depart' => $depart,
            'tarifs' => $depart->getTarifs(),
        ]);
    }

    #[Route('/step2', name: 'app_reservation_step2', methods: ['POST'])]
    public function step2(
        Request $request,
        SessionInterface $session,
        DepartRepository $departRepository
    ): Response {
        $reservationData = $session->get('reservation_data');
        
        if (!$reservationData || $reservationData['step'] !== 1) {
            return $this->redirectToRoute('app_packages_index');
        }

        $typeChambre = $request->request->get('type_chambre');
        $nbPelerins = (int) $request->request->get('nb_pelerins');

        if (!$typeChambre || $nbPelerins < 1 || $nbPelerins > 4) {
            $this->addFlash('error', 'Veuillez sélectionner un type de chambre et un nombre de pèlerins valide.');
            return $this->redirectToRoute('app_reservation_step1', ['departId' => $reservationData['depart_id']]);
        }

        $depart = $departRepository->find($reservationData['depart_id']);
        
        if (!$depart->canAccommodate($nbPelerins)) {
            $this->addFlash('error', 'Pas assez de places disponibles pour ce nombre de pèlerins.');
            return $this->redirectToRoute('app_reservation_step1', ['departId' => $reservationData['depart_id']]);
        }

        // Calculer le prix total
        $tarif = $depart->getTarifForChambre($typeChambre);
        if (!$tarif) {
            // Si aucun tarif spécifique n'existe, utiliser le prix de base du package
            $prixBase = $depart->getPackage()->getPrixBase() ?? 0;
            $prixTotal = $prixBase * $nbPelerins;
            $tarifId = null; // Pas de tarif spécifique
        } else {
        $prixTotal = $tarif->getPrixFinal() * $nbPelerins;
            $tarifId = $tarif->getId();
        }

        // Mettre à jour les données de session
        $reservationData['type_chambre'] = $typeChambre;
        $reservationData['nb_pelerins'] = $nbPelerins;
        $reservationData['prix_total'] = $prixTotal;
        $reservationData['tarif_id'] = $tarifId;
        $reservationData['step'] = 2;
        
        $session->set('reservation_data', $reservationData);

        return $this->render('reservation/step2.html.twig', [
            'depart' => $depart,
            'tarif' => $tarif,
            'nb_pelerins' => $nbPelerins,
            'prix_total' => $prixTotal,
        ]);
    }

    #[Route('/step3', name: 'app_reservation_step3', methods: ['POST'])]
    public function step3(
        Request $request,
        SessionInterface $session,
        DepartRepository $departRepository
    ): Response {
        $reservationData = $session->get('reservation_data');
        
        if (!$reservationData || $reservationData['step'] !== 2) {
            return $this->redirectToRoute('app_packages_index');
        }

        // Récupérer les données des pèlerins
        $pelerinsData = [];
        for ($i = 0; $i < $reservationData['nb_pelerins']; $i++) {
            $pelerinsData[] = [
                'nom' => $request->request->get("pelerin_{$i}_nom"),
                'prenom' => $request->request->get("pelerin_{$i}_prenom"),
                'date_naissance' => $request->request->get("pelerin_{$i}_date_naissance"),
                'nationalite' => $request->request->get("pelerin_{$i}_nationalite"),
                'sexe' => $request->request->get("pelerin_{$i}_sexe"),
                'passport_number' => $request->request->get("pelerin_{$i}_passport_number"),
                'passport_expiry' => $request->request->get("pelerin_{$i}_passport_expiry"),
                'lieu_naissance' => $request->request->get("pelerin_{$i}_lieu_naissance"),
                'phone' => $request->request->get("pelerin_{$i}_phone"),
                'adresse' => $request->request->get("pelerin_{$i}_adresse"),
                'nom_urgence' => $request->request->get("pelerin_{$i}_nom_urgence"),
                'phone_urgence' => $request->request->get("pelerin_{$i}_phone_urgence"),
                'relation_urgence' => $request->request->get("pelerin_{$i}_relation_urgence"),
            ];
        }

        // Validation basique
        foreach ($pelerinsData as $index => $pelerinData) {
            if (empty($pelerinData['nom']) || empty($pelerinData['prenom']) || empty($pelerinData['date_naissance'])) {
                $this->addFlash('error', "Veuillez remplir tous les champs obligatoires pour le pèlerin " . ($index + 1));
                return $this->redirectToRoute('app_reservation_step2');
            }
        }

        $depart = $departRepository->find($reservationData['depart_id']);
        
        // Mettre à jour les données de session
        $reservationData['pelerins'] = $pelerinsData;
        $reservationData['step'] = 3;
        $session->set('reservation_data', $reservationData);

        return $this->render('reservation/step3.html.twig', [
            'depart' => $depart,
            'reservation_data' => $reservationData,
            'pelerins' => $pelerinsData,
        ]);
    }

    #[Route('/confirm', name: 'app_reservation_confirm', methods: ['POST'])]
    public function confirm(
        Request $request,
        SessionInterface $session,
        EntityManagerInterface $em,
        DepartRepository $departRepository
    ): Response {
        $reservationData = $session->get('reservation_data');
        
        if (!$reservationData || $reservationData['step'] !== 3) {
            return $this->redirectToRoute('app_packages_index');
        }

        $depart = $departRepository->find($reservationData['depart_id']);
        
        if (!$depart->canAccommodate($reservationData['nb_pelerins'])) {
            $this->addFlash('error', 'Plus assez de places disponibles.');
            return $this->redirectToRoute('app_packages_index');
        }

        try {
            $em->beginTransaction();

            // Créer la réservation
            $reservation = new Reservation();
            $reservation->setUser($this->getUser());
            $reservation->setDepart($depart);
            $reservation->setTypeChambre($reservationData['type_chambre']);
            $reservation->setNbPelerins($reservationData['nb_pelerins']);
            $reservation->setTotal((string) $reservationData['prix_total']);
            $reservation->setStatut(Reservation::STATUT_EN_ATTENTE_APPROBATION);
            
            // Calculer l'acompte (30% par défaut)
            $reservation->calculateAcompte(30);

            $em->persist($reservation);

            // Créer les pèlerins
            foreach ($reservationData['pelerins'] as $pelerinData) {
                $pelerin = new Pelerin();
                $pelerin->setReservation($reservation);
                $pelerin->setNom($pelerinData['nom']);
                $pelerin->setPrenom($pelerinData['prenom']);
                $pelerin->setDateNaissance(new \DateTimeImmutable($pelerinData['date_naissance']));
                $pelerin->setNationalite($pelerinData['nationalite']);
                $pelerin->setSexe($pelerinData['sexe']);
                
                // Nouveaux champs
                if (!empty($pelerinData['passport_number'])) {
                    $pelerin->setPassportNumber($pelerinData['passport_number']);
                }
                if (!empty($pelerinData['passport_expiry'])) {
                    $pelerin->setPassportExpiry(new \DateTimeImmutable($pelerinData['passport_expiry']));
                }
                if (!empty($pelerinData['lieu_naissance'])) {
                    $pelerin->setLieuNaissance($pelerinData['lieu_naissance']);
                }
                if (!empty($pelerinData['phone'])) {
                    $pelerin->setPhone($pelerinData['phone']);
                }
                if (!empty($pelerinData['adresse'])) {
                    $pelerin->setAdresse($pelerinData['adresse']);
                }
                if (!empty($pelerinData['nom_urgence'])) {
                    $pelerin->setNomUrgence($pelerinData['nom_urgence']);
                }
                if (!empty($pelerinData['phone_urgence'])) {
                    $pelerin->setPhoneUrgence($pelerinData['phone_urgence']);
                }
                if (!empty($pelerinData['relation_urgence'])) {
                    $pelerin->setRelationUrgence($pelerinData['relation_urgence']);
                }

                $em->persist($pelerin);
                $reservation->addPelerin($pelerin);
            }

            // Mettre à jour le quota vendu
            $depart->setQuotaVendu($depart->getQuotaVendu() + $reservationData['nb_pelerins']);

            $em->flush();
            $em->commit();

            // Nettoyer la session
            $session->remove('reservation_data');

            $this->addFlash('success', 'Votre réservation a été créée avec succès !');
            
            return $this->redirectToRoute('app_reservation_payment', ['id' => $reservation->getId()]);

        } catch (\Exception $e) {
            $em->rollback();
            $this->addFlash('error', 'Une erreur est survenue lors de la création de votre réservation.');
            return $this->redirectToRoute('app_packages_index');
        }
    }

    #[Route('/{id}/payment', name: 'app_reservation_payment', methods: ['GET', 'POST'])]
    public function payment(
        Request $request,
        Reservation $reservation,
        EntityManagerInterface $em
    ): Response {
        // Vérifier que l'utilisateur peut accéder à cette réservation
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_PAIEMENT) {
            $this->addFlash('info', 'Cette réservation ne nécessite pas de paiement.');
            return $this->redirectToRoute('app_client_dashboard');
        }

        if ($request->isMethod('POST')) {
            $paymentMethod = $request->request->get('payment_method');
            
            // Simuler un traitement de paiement réussi
            // En production, vous intégreriez ici un vrai système de paiement
            
            try {
                // Créer un enregistrement de paiement
                $paiement = new Paiement();
                $paiement->setReservation($reservation);
                $paiement->setMontant($reservation->getTotal());
                $paiement->setMode($paymentMethod);
                $paiement->setStatut(Paiement::STATUT_VALIDE);
                
                $em->persist($paiement);
                
                // Mettre à jour le statut de la réservation
                $reservation->setStatut(Reservation::STATUT_CONFIRME);
                $reservation->setAcompte($reservation->getTotal());
                $reservation->setReste(0);
                
                $em->flush();
                
                $this->addFlash('success', 'Paiement traité avec succès ! Votre réservation est maintenant confirmée.');
                return $this->redirectToRoute('app_reservation_show', ['id' => $reservation->getId()]);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du traitement du paiement.');
            }
        }

        return $this->render('reservation/payment.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show')]
    public function show(Reservation $reservation): Response
    {
        // Vérifier que l'utilisateur peut accéder à cette réservation
        if ($reservation->getUser() !== $this->getUser() && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }
} 