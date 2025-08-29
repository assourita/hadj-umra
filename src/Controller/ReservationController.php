<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Package;
use App\Form\ReservationType;
use App\Repository\PackageRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reservation')]
class ReservationController extends AbstractController
{
    #[Route('/package/{id}', name: 'app_reservation_package', methods: ['GET'])]
    public function packageDetails(Package $package): Response
    {
        return $this->render('reservation/details.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/new/{packageId}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PackageRepository $packageRepository, int $packageId): Response
    {
        $package = $packageRepository->find($packageId);
        
        if (!$package) {
            throw $this->createNotFoundException('Package non trouvé');
        }

        $reservation = new Reservation();
        $reservation->setPackage($package);
        $reservation->setTotalPrice($package->getPrice());

        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Votre réservation a été créée avec succès !');

            return $this->redirectToRoute('app_payment_form', ['reservationId' => $reservation->getId()]);
        }

        return $this->render('reservation/form.html.twig', [
            'reservation' => $reservation,
            'package' => $package,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(Reservation $reservation): Response
    {
        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Réservation mise à jour avec succès !');

            return $this->redirectToRoute('app_reservation_show', ['id' => $reservation->getId()]);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Réservation supprimée avec succès !');
        }

        return $this->redirectToRoute('app_dashboard');
    }

    #[Route('/confirmation/{id}', name: 'app_reservation_confirmation', methods: ['GET'])]
    public function confirmation(Reservation $reservation): Response
    {
        return $this->render('reservation/confirmation.html.twig', [
            'reservation' => $reservation,
        ]);
    }
}
