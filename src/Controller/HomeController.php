<?php

namespace App\Controller;

use App\Entity\ContactMessage;
use App\Repository\PackageRepository;
use App\Repository\DepartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PackageRepository $packageRepository,
        DepartRepository $departRepository
    ): Response {
        // Packages populaires pour la page d'accueil
        $popularPackages = $packageRepository->findPopularPackages(6);
        
        // Départs prochains
        $upcomingDeparts = $departRepository->findUpcomingDeparts();
        
        // Packages récents
        $recentPackages = $packageRepository->findRecentPackages(3);

        return $this->render('home/index.html.twig', [
            'packages' => $popularPackages,
            'upcoming_departs' => $upcomingDeparts,
            'recent_packages' => $recentPackages,
        ]);
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/contact', name: 'app_contact', methods: ['GET', 'POST'])]
    public function contact(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Récupération des données du formulaire
            $nom = trim($request->request->get('nom', ''));
            $email = trim($request->request->get('email', ''));
            $telephone = trim($request->request->get('telephone', ''));
            $pays = trim($request->request->get('pays', ''));
            $sujet = trim($request->request->get('sujet', ''));
            $message = trim($request->request->get('message', ''));
            $newsletter = $request->request->has('newsletter');
            $rgpd = $request->request->has('rgpd');

            // Validation des champs obligatoires
            $errors = [];
            
            if (empty($nom)) {
                $errors[] = 'Le nom est obligatoire';
            }
            
            if (empty($email)) {
                $errors[] = 'L\'email est obligatoire';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Veuillez saisir un email valide';
            }
            
            if (empty($sujet)) {
                $errors[] = 'Le sujet est obligatoire';
            }
            
            if (empty($message)) {
                $errors[] = 'Le message est obligatoire';
            }
            
            if (!$rgpd) {
                $errors[] = 'Vous devez accepter la politique de confidentialité';
            }

            if (empty($errors)) {
                // Création du message de contact
                $contactMessage = new ContactMessage();
                $contactMessage->setNom($nom);
                $contactMessage->setEmail($email);
                $contactMessage->setTelephone($telephone ?: null);
                $contactMessage->setPays($pays ?: null);
                $contactMessage->setSujet($sujet);
                $contactMessage->setMessage($message);
                $contactMessage->setNewsletter($newsletter);
                $contactMessage->setRgpd($rgpd);
                
                // Lier le message à l'utilisateur connecté s'il est connecté
                if ($this->getUser()) {
                    $contactMessage->setUser($this->getUser());
                }

                // Sauvegarde en base de données
                $entityManager->persist($contactMessage);
                $entityManager->flush();

                $this->addFlash('success', 'Votre message a été envoyé avec succès ! Nous vous répondrons dans les plus brefs délais.');
                
                return $this->redirectToRoute('app_contact');
            } else {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error);
                }
            }
        }

        return $this->render('home/contact.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }
} 