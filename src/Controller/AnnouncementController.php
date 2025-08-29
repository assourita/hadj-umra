<?php

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnnouncementController extends AbstractController
{
    #[Route('/annonces', name: 'app_announcements')]
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        $announcements = $announcementRepository->findPublished();

        return $this->render('announcement/index.html.twig', [
            'announcements' => $announcements,
        ]);
    }

    #[Route('/annonces/{id}', name: 'app_announcement_show')]
    public function show(int $id, AnnouncementRepository $announcementRepository): Response
    {
        $announcement = $announcementRepository->find($id);

        if (!$announcement || !$announcement->isPublished()) {
            throw $this->createNotFoundException('Annonce non trouvée ou non publiée.');
        }

        return $this->render('announcement/show.html.twig', [
            'announcement' => $announcement,
        ]);
    }

    #[Route('/annonces/type/{type}', name: 'app_announcements_by_type')]
    public function byType(string $type, AnnouncementRepository $announcementRepository): Response
    {
        $announcements = $announcementRepository->findPublishedByType($type);

        return $this->render('announcement/index.html.twig', [
            'announcements' => $announcements,
            'type' => $type,
        ]);
    }
}
