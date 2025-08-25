<?php

namespace App\Controller\Admin;

use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/reservations')]
#[IsGranted('ROLE_ADMIN')]
class ReservationController extends AbstractController
{
    #[Route('/', name: 'app_admin_reservations_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $reservations = $reservationRepository->findAllOrderedByDate();

        return $this->render('admin/reservations/index.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/pending', name: 'app_admin_reservations_pending')]
    public function pending(ReservationRepository $reservationRepository): Response
    {
        $pendingReservations = $reservationRepository->findByStatut(Reservation::STATUT_EN_ATTENTE_APPROBATION);

        return $this->render('admin/reservations/pending.html.twig', [
            'reservations' => $pendingReservations,
        ]);
    }

    #[Route('/documents', name: 'app_admin_reservations_documents')]
    public function documents(ReservationRepository $reservationRepository): Response
    {
        $documentReservations = $reservationRepository->findByStatut(Reservation::STATUT_EN_ATTENTE_DOCUMENTS);

        return $this->render('admin/reservations/documents.html.twig', [
            'reservations' => $documentReservations,
        ]);
    }

    #[Route('/{id}/approve', name: 'app_admin_reservation_approve', methods: ['POST'])]
    public function approve(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_APPROBATION) {
            $this->addFlash('error', 'Cette réservation ne peut pas être approuvée.');
            return $this->redirectToRoute('app_admin_reservations_pending');
        }

        $reservation->setStatut(Reservation::STATUT_EN_ATTENTE_DOCUMENTS);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        
        // Définir une date limite pour les documents (par exemple 7 jours)
        $reservation->setDateLimiteDocument(new \DateTimeImmutable('+7 days'));
        
        $entityManager->flush();

        $this->addFlash('success', 'Réservation approuvée ! Le client doit maintenant fournir les documents requis avant de pouvoir procéder au paiement.');
        
        return $this->redirectToRoute('app_admin_reservations_pending');
    }

    #[Route('/{id}/reject', name: 'app_admin_reservation_reject', methods: ['POST'])]
    public function reject(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_APPROBATION) {
            $this->addFlash('error', 'Cette réservation ne peut pas être rejetée.');
            return $this->redirectToRoute('app_admin_reservations_pending');
        }

        $raison = $request->request->get('raison', 'Réservation rejetée par l\'administrateur');
        
        $reservation->setStatut(Reservation::STATUT_ANNULE);
        $reservation->setRemarques($raison);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        
        // Libérer les places
        $depart = $reservation->getDepart();
        $depart->setQuotaVendu($depart->getQuotaVendu() - $reservation->getNbPelerins());
        
        $entityManager->flush();

        $this->addFlash('success', 'Réservation rejetée avec succès.');
        
        return $this->redirectToRoute('app_admin_reservations_pending');
    }

    #[Route('/{id}/validate-documents', name: 'app_admin_reservation_validate_documents', methods: ['POST'])]
    public function validateDocuments(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_DOCUMENTS) {
            $this->addFlash('error', 'Cette réservation ne peut pas être validée.');
            return $this->redirectToRoute('app_admin_reservations_pending');
        }

        // Vérifier que tous les documents obligatoires sont fournis
        $documentsRequis = $reservation->getDepart()->getPackage()->getDocumentsRequis();
        $documentsFournis = [];
        
        foreach ($reservation->getPelerins() as $pelerin) {
            foreach ($pelerin->getDocuments() as $document) {
                $documentsFournis[$document->getType()] = true;
            }
        }

        $documentsManquants = [];
        foreach ($documentsRequis as $type => $doc) {
            if ($doc['obligatoire'] && !isset($documentsFournis[$type])) {
                $documentsManquants[] = $doc['nom'];
            }
        }

        if (!empty($documentsManquants)) {
            $this->addFlash('error', 'Documents manquants : ' . implode(', ', $documentsManquants));
            return $this->redirectToRoute('app_admin_reservation_show', ['id' => $reservation->getId()]);
        }

        $reservation->setStatut(Reservation::STATUT_EN_ATTENTE_PAIEMENT);
        $reservation->setUpdatedAt(new \DateTimeImmutable());
        
        $entityManager->flush();

        $this->addFlash('success', 'Documents validés ! Le client peut maintenant procéder au paiement.');
        
        return $this->redirectToRoute('app_admin_reservation_show', ['id' => $reservation->getId()]);
    }

    #[Route('/{id}/show', name: 'app_admin_reservation_show')]
    public function show(Reservation $reservation): Response
    {
        return $this->render('admin/reservations/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
