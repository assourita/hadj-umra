<?php

namespace App\Controller\Dashboard;

use App\Entity\Announcement;
use App\Form\AnnouncementType;
use App\Repository\AnnouncementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard/announcement')]
class AnnouncementController extends AbstractController
{
    #[Route('/', name: 'dashboard_announcement_index', methods: ['GET'])]
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        return $this->render('dashboard/announcement/index.html.twig', [
            'announcements' => $announcementRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'dashboard_announcement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $announcement = new Announcement();
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('announcements_directory'),
                        $newFilename
                    );
                    $announcement->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Si l'annonce est publiée, définir la date de publication
            if ($announcement->isIsPublished()) {
                $announcement->setPublishedAt(new \DateTime());
            }

            $entityManager->persist($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'Annonce créée avec succès !');
            return $this->redirectToRoute('dashboard_announcement_index');
        }

        return $this->render('dashboard/announcement/new.html.twig', [
            'announcement' => $announcement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dashboard_announcement_show', methods: ['GET'])]
    public function show(Announcement $announcement): Response
    {
        return $this->render('dashboard/announcement/show.html.twig', [
            'announcement' => $announcement,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard_announcement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Announcement $announcement, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(AnnouncementType::class, $announcement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('announcements_directory'),
                        $newFilename
                    );
                    $announcement->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            // Si l'annonce est publiée et n'avait pas de date de publication, la définir
            if ($announcement->isIsPublished() && $announcement->getPublishedAt() === null) {
                $announcement->setPublishedAt(new \DateTime());
            }

            $entityManager->flush();

            $this->addFlash('success', 'Annonce modifiée avec succès !');
            return $this->redirectToRoute('dashboard_announcement_index');
        }

        return $this->render('dashboard/announcement/edit.html.twig', [
            'announcement' => $announcement,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dashboard_announcement_delete', methods: ['POST'])]
    public function delete(Request $request, Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$announcement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($announcement);
            $entityManager->flush();

            $this->addFlash('success', 'Annonce supprimée avec succès !');
        }

        return $this->redirectToRoute('dashboard_announcement_index');
    }

    #[Route('/{id}/toggle-status', name: 'dashboard_announcement_toggle_status', methods: ['POST'])]
    public function toggleStatus(Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        $announcement->setIsPublished(!$announcement->isIsPublished());
        
        if ($announcement->isIsPublished() && $announcement->getPublishedAt() === null) {
            $announcement->setPublishedAt(new \DateTime());
        }

        $entityManager->flush();

        $status = $announcement->isIsPublished() ? 'publiée' : 'dépubliée';
        $this->addFlash('success', "Annonce {$status} avec succès !");

        return $this->redirectToRoute('dashboard_announcement_index');
    }

    #[Route('/{id}/publish', name: 'dashboard_announcement_publish', methods: ['POST'])]
    public function publish(Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        $announcement->setIsPublished(true);
        $announcement->setPublishedAt(new \DateTime());
        
        $entityManager->flush();

        $this->addFlash('success', 'Annonce publiée avec succès !');

        return $this->redirectToRoute('dashboard_announcement_index');
    }

    #[Route('/{id}/unpublish', name: 'dashboard_announcement_unpublish', methods: ['POST'])]
    public function unpublish(Announcement $announcement, EntityManagerInterface $entityManager): Response
    {
        $announcement->setIsPublished(false);
        $announcement->setPublishedAt(null);
        
        $entityManager->flush();

        $this->addFlash('success', 'Annonce dépubliée avec succès !');

        return $this->redirectToRoute('dashboard_announcement_index');
    }
}
