<?php

namespace App\Controller\Admin;

use App\Entity\Tarif;
use App\Repository\TarifRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/tarifs')]
#[IsGranted('ROLE_ADMIN')]
class TarifController extends AbstractController
{
    #[Route('/', name: 'app_admin_tarifs_index')]
    public function index(TarifRepository $tarifRepository): Response
    {
        $tarifs = $tarifRepository->findAllWithDepartAndPackage();

        return $this->render('admin/tarifs/index.html.twig', [
            'tarifs' => $tarifs,
        ]);
    }

    #[Route('/{id}/show', name: 'app_admin_tarif_show')]
    public function show(Tarif $tarif): Response
    {
        return $this->render('admin/tarifs/show.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_tarif_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tarif $tarif, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $prix = $request->request->get('prix');
            $reduction = $request->request->get('reduction');
            $isActive = $request->request->has('is_active');

            $tarif->setPrix((float) $prix);
            $tarif->setReduction((float) $reduction);
            $tarif->setIsActive($isActive);

            $entityManager->flush();

            $this->addFlash('success', 'Tarif mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_tarifs_index');
        }

        return $this->render('admin/tarifs/edit.html.twig', [
            'tarif' => $tarif,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_tarif_delete', methods: ['POST'])]
    public function delete(Tarif $tarif, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($tarif);
        $entityManager->flush();

        $this->addFlash('success', 'Tarif supprimé avec succès !');
        return $this->redirectToRoute('app_admin_tarifs_index');
    }
}
