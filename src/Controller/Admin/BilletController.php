<?php

namespace App\Controller\Admin;

use App\Entity\Billet;
use App\Repository\BilletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/billets')]
#[IsGranted('ROLE_ADMIN')]
class BilletController extends AbstractController
{
    #[Route('/', name: 'app_admin_billets_index')]
    public function index(BilletRepository $billetRepository): Response
    {
        $billets = $billetRepository->findAllWithPelerin();

        return $this->render('admin/billets/index.html.twig', [
            'billets' => $billets,
        ]);
    }

    #[Route('/{id}/show', name: 'app_admin_billet_show')]
    public function show(Billet $billet): Response
    {
        return $this->render('admin/billets/show.html.twig', [
            'billet' => $billet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_billet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Billet $billet, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $numero = $request->request->get('numero');
            $compagnie = $request->request->get('compagnie');
            $classe = $request->request->get('classe');
            $dateDepart = $request->request->get('date_depart');
            $dateRetour = $request->request->get('date_retour');
            $statut = $request->request->get('statut');
            $remarques = $request->request->get('remarques');

            $billet->setNumero($numero);
            $billet->setCompagnie($compagnie);
            $billet->setClasse($classe);
            if ($dateDepart) {
                $billet->setDateDepart(new \DateTimeImmutable($dateDepart));
            }
            if ($dateRetour) {
                $billet->setDateRetour(new \DateTimeImmutable($dateRetour));
            }
            $billet->setStatut($statut);
            $billet->setRemarques($remarques);

            $entityManager->flush();

            $this->addFlash('success', 'Billet mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_billets_index');
        }

        return $this->render('admin/billets/edit.html.twig', [
            'billet' => $billet,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_billet_delete', methods: ['POST'])]
    public function delete(Billet $billet, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($billet);
        $entityManager->flush();

        $this->addFlash('success', 'Billet supprimé avec succès !');
        return $this->redirectToRoute('app_admin_billets_index');
    }
}
