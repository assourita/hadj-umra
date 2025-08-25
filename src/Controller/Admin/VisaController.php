<?php

namespace App\Controller\Admin;

use App\Entity\Visa;
use App\Repository\VisaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/visas')]
#[IsGranted('ROLE_ADMIN')]
class VisaController extends AbstractController
{
    #[Route('/', name: 'app_admin_visas_index')]
    public function index(VisaRepository $visaRepository): Response
    {
        $visas = $visaRepository->findAllWithPelerin();

        return $this->render('admin/visas/index.html.twig', [
            'visas' => $visas,
        ]);
    }

    #[Route('/{id}/show', name: 'app_admin_visa_show')]
    public function show(Visa $visa): Response
    {
        return $this->render('admin/visas/show.html.twig', [
            'visa' => $visa,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_visa_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Visa $visa, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $numero = $request->request->get('numero');
            $dateEmission = $request->request->get('date_emission');
            $dateExpiration = $request->request->get('date_expiration');
            $statut = $request->request->get('statut');
            $remarques = $request->request->get('remarques');

            $visa->setNumero($numero);
            if ($dateEmission) {
                $visa->setDateEmission(new \DateTimeImmutable($dateEmission));
            }
            if ($dateExpiration) {
                $visa->setDateExpiration(new \DateTimeImmutable($dateExpiration));
            }
            $visa->setStatut($statut);
            $visa->setRemarques($remarques);

            $entityManager->flush();

            $this->addFlash('success', 'Visa mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_visas_index');
        }

        return $this->render('admin/visas/edit.html.twig', [
            'visa' => $visa,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_visa_delete', methods: ['POST'])]
    public function delete(Visa $visa, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($visa);
        $entityManager->flush();

        $this->addFlash('success', 'Visa supprimé avec succès !');
        return $this->redirectToRoute('app_admin_visas_index');
    }
}
