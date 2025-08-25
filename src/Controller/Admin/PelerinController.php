<?php

namespace App\Controller\Admin;

use App\Entity\Pelerin;
use App\Repository\PelerinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/pelerins')]
#[IsGranted('ROLE_ADMIN')]
class PelerinController extends AbstractController
{
    #[Route('/', name: 'app_admin_pelerins_index')]
    public function index(PelerinRepository $pelerinRepository): Response
    {
        $pelerins = $pelerinRepository->findAllWithReservation();

        return $this->render('admin/pelerins/index.html.twig', [
            'pelerins' => $pelerins,
        ]);
    }

    #[Route('/{id}/show', name: 'app_admin_pelerin_show')]
    public function show(Pelerin $pelerin): Response
    {
        return $this->render('admin/pelerins/show.html.twig', [
            'pelerin' => $pelerin,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_pelerin_edit', methods: ['GET', 'POST'])]
    public function modifier(Request $request, Pelerin $pelerin, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $nom = $request->request->get('nom');
            $prenom = $request->request->get('prenom');
            $dateNaissance = $request->request->get('date_naissance');
            $nationalite = $request->request->get('nationalite');
            $sexe = $request->request->get('sexe');
            $passportNumber = $request->request->get('passport_number');
            $passportExpiry = $request->request->get('passport_expiry');
            $lieuNaissance = $request->request->get('lieu_naissance');
            $phone = $request->request->get('phone');
            $adresse = $request->request->get('adresse');
            $nomUrgence = $request->request->get('nom_urgence');
            $phoneUrgence = $request->request->get('phone_urgence');
            $relationUrgence = $request->request->get('relation_urgence');

            $pelerin->setNom($nom);
            $pelerin->setPrenom($prenom);
            if ($dateNaissance) {
                $pelerin->setDateNaissance(new \DateTimeImmutable($dateNaissance));
            }
            $pelerin->setNationalite($nationalite);
            $pelerin->setSexe($sexe);
            $pelerin->setPassportNumber($passportNumber);
            if ($passportExpiry) {
                $pelerin->setPassportExpiry(new \DateTimeImmutable($passportExpiry));
            }
            $pelerin->setLieuNaissance($lieuNaissance);
            $pelerin->setPhone($phone);
            $pelerin->setAdresse($adresse);
            $pelerin->setNomUrgence($nomUrgence);
            $pelerin->setPhoneUrgence($phoneUrgence);
            $pelerin->setRelationUrgence($relationUrgence);
            $pelerin->setUpdatedAt(new \DateTimeImmutable());

            $entityManager->flush();

            $this->addFlash('success', 'Pèlerin mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_pelerins_index');
        }

        return $this->render('admin/pelerins/edit.html.twig', [
            'pelerin' => $pelerin,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_pelerin_delete', methods: ['POST'])]
    public function delete(Pelerin $pelerin, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($pelerin);
        $entityManager->flush();

        $this->addFlash('success', 'Pèlerin supprimé avec succès !');
        return $this->redirectToRoute('app_admin_pelerins_index');
    }
}
