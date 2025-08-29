<?php

namespace App\Controller;

use App\Repository\AnnouncementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TravelController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AnnouncementRepository $announcementRepository): Response
    {
        $announcements = $announcementRepository->findPublished();
        
        return $this->render('travel/index.html.twig', [
            'announcements' => $announcements,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('travel/about.html.twig');
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('travel/blog.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('travel/contact.html.twig');
    }

    #[Route('/elements', name: 'app_elements')]
    public function elements(): Response
    {
        return $this->render('travel/elements.html.twig');
    }

    #[Route('/offers', name: 'app_offers')]
    public function offers(): Response
    {
        return $this->render('travel/offers.html.twig');
    }

    #[Route('/single-listing', name: 'app_single_listing')]
    public function singleListing(): Response
    {
        return $this->render('travel/single_listing.html.twig');
    }
}
