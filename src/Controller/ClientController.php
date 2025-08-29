<?php

namespace App\Controller;

use App\Entity\Document;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\ContactMessageRepository;

#[Route('/client')]
#[IsGranted('ROLE_USER')]
class ClientController extends AbstractController
{
    #[Route('/dashboard', name: 'app_client_dashboard')]
    public function dashboard(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findByUser($user);

        // Calculer les statistiques
        $stats = [
            'total_reservations' => count($reservations),
            'reservations_confirmees' => count(array_filter($reservations, fn($r) => $r->getStatut() === 'confirme')),
            'en_attente_paiement' => count(array_filter($reservations, fn($r) => $r->getStatut() === 'en_attente_paiement')),
            'en_attente_documents' => count(array_filter($reservations, fn($r) => $r->getStatut() === 'en_attente_documents')),
        ];

        return $this->render('client/dashboard.html.twig', [
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }

    #[Route('/reservations', name: 'app_client_reservations')]
    public function reservations(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findByUser($user);

        return $this->render('client/reservations.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/documents', name: 'app_documents_index')]
    public function documentsIndex(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        
        return $this->render('client/documents.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/messages', name: 'app_client_messages')]
    public function messagesIndex(ContactMessageRepository $contactMessageRepository): Response
    {
        $user = $this->getUser();
        $userEmail = $user->getEmail();
        
        // Recherche les messages par utilisateur OU par email
        $messages = $contactMessageRepository->createQueryBuilder('m')
            ->where('m.user = :user OR LOWER(m.email) = LOWER(:email)')
            ->setParameter('user', $user)
            ->setParameter('email', $userEmail)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('client/messages.html.twig', [
            'messages' => $messages,
            'userEmail' => $userEmail,
        ]);
    }

    #[Route('/reservation/{id}/documents', name: 'app_client_documents')]
    public function documents(
        int $id,
        ReservationRepository $reservationRepository
    ): Response {
        $user = $this->getUser();
        $reservation = $reservationRepository->find($id);
        
        if (!$reservation || $reservation->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        // Récupérer toutes les réservations de l'utilisateur pour le template
        $reservations = $reservationRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('client/documents.html.twig', [
            'reservation' => $reservation,
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/{reservationId}/pelerin/{pelerinId}/upload', name: 'app_client_upload_document', methods: ['POST'])]
    public function uploadDocument(
        int $reservationId,
        int $pelerinId,
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger,
        ReservationRepository $reservationRepository
    ): Response {
        $reservation = $reservationRepository->find($reservationId);
        
        if (!$reservation || $reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $pelerin = null;
        foreach ($reservation->getPelerins() as $p) {
            if ($p->getId() === $pelerinId) {
                $pelerin = $p;
                break;
            }
        }

        if (!$pelerin) {
            throw $this->createNotFoundException('Pèlerin non trouvé');
        }

        $uploadedFile = $request->files->get('document');
        $documentType = $request->request->get('type');

        if (!$uploadedFile) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');
            return $this->redirectToRoute('app_client_documents', ['id' => $reservationId]);
        }

        // Validation du type de fichier
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($uploadedFile->getMimeType(), $allowedMimeTypes)) {
            $this->addFlash('error', 'Type de fichier non autorisé. Seuls les fichiers JPG, PNG et PDF sont acceptés.');
            return $this->redirectToRoute('app_client_documents', ['id' => $reservationId]);
        }

        // Validation de la taille (5MB max)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximum : 5MB.');
            return $this->redirectToRoute('app_client_documents', ['id' => $reservationId]);
        }

        try {
            // Récupérer les informations du fichier AVANT de le déplacer
            $fileSize = $uploadedFile->getSize();
            $mimeType = $uploadedFile->getMimeType();
            
            // Créer le nom de fichier sécurisé
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->guessExtension();

            // Déplacer le fichier
            $uploadDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }
            
            $uploadedFile->move($uploadDirectory, $newFilename);

            // Créer l'entité Document avec les informations récupérées avant le déplacement
            $document = new Document();
            $document->setPelerin($pelerin);
            $document->setType($documentType);
            $document->setFileName($newFilename);
            $document->setFileSize($fileSize);
            $document->setMimeType($mimeType);
            $document->setUrl('/uploads/documents/' . $newFilename);

            $em->persist($document);
            $em->flush();

            $this->addFlash('success', 'Document téléversé avec succès.');

        } catch (FileException $e) {
            $this->addFlash('error', 'Erreur lors du téléversement du fichier.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur inattendue s\'est produite lors du téléversement.');
        }

        return $this->redirectToRoute('app_client_documents', ['id' => $reservationId]);
    }

    #[Route('/profile', name: 'app_client_profile')]
    public function profile(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        
        // Récupérer les réservations de l'utilisateur
        $reservations = $reservationRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        
        // Calculer les statistiques
        $stats = [
            'total_reservations' => count($reservations),
            'reservations_confirmees' => count(array_filter($reservations, fn($r) => $r->getStatut() === 'confirme')),
            'en_attente_paiement' => count(array_filter($reservations, fn($r) => $r->getStatut() === 'en_attente_paiement')),
        ];
        
        return $this->render('client/profile.html.twig', [
            'reservations' => $reservations,
            'stats' => $stats,
        ]);
    }

    #[Route('/profile/edit', name: 'app_client_profile_edit', methods: ['GET', 'POST'])]
    public function editProfile(
        Request $request,
        EntityManagerInterface $em,
        FormFactoryInterface $formFactory,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        // Créer le formulaire
        $form = $formFactory->createBuilder()
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'data' => $user->getPrenom(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'data' => $user->getNom(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'data' => $user->getEmail(),
                'attr' => ['class' => 'form-control']
            ])
            ->add('phone', TextType::class, [
                'label' => 'Téléphone',
                'data' => $user->getPhone(),
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Nouveau mot de passe',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            
            $user->setPrenom($data['prenom']);
            $user->setNom($data['nom']);
            $user->setEmail($data['email']);
            $user->setPhone($data['phone']);
            
            // Mettre à jour le mot de passe si fourni
            if (!empty($data['plainPassword'])) {
                $user->setPassword($passwordHasher->hashPassword($user, $data['plainPassword']));
            }

            $em->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');
            return $this->redirectToRoute('app_client_dashboard');
        }

        return $this->render('client/profile_edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
} 