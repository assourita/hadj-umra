<?php

namespace App\Controller;

use App\Entity\Package;
use App\Entity\News;
use App\Entity\Contact;
use App\Repository\PackageRepository;
use App\Repository\NewsRepository;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard")
 */
class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="dashboard_index", methods={"GET"})
     */
    public function index(
        PackageRepository $packageRepository,
        NewsRepository $newsRepository,
        ContactRepository $contactRepository
    ): Response {
        return $this->render('dashboard/index.html.twig', [
            'packages' => $packageRepository->findAll(),
            'news' => $newsRepository->findAll(),
            'unreadMessages' => $contactRepository->countUnreadMessages(),
            'recentMessages' => $contactRepository->findRecentMessages(5),
        ]);
    }

    /**
     * @Route("/packages", name="dashboard_packages", methods={"GET"})
     */
    public function packages(PackageRepository $packageRepository): Response
    {
        return $this->render('dashboard/packages.html.twig', [
            'packages' => $packageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/news", name="dashboard_news", methods={"GET"})
     */
    public function news(NewsRepository $newsRepository): Response
    {
        return $this->render('dashboard/news.html.twig', [
            'news' => $newsRepository->findAll(),
        ]);
    }

    /**
     * @Route("/contacts", name="dashboard_contacts", methods={"GET"})
     */
    public function contacts(ContactRepository $contactRepository): Response
    {
        return $this->render('dashboard/contacts.html.twig', [
            'contacts' => $contactRepository->findAll(),
            'unreadCount' => $contactRepository->countUnreadMessages(),
        ]);
    }
}
