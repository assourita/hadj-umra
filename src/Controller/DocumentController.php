<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\Pelerin;
use App\Entity\Reservation;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/documents')]
#[IsGranted('ROLE_USER')]
class DocumentController extends AbstractController
{
    #[Route('/', name: 'app_documents_index')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        $user = $this->getUser();
        $reservations = $reservationRepository->findByUser($user);

        return $this->render('client/documents.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    #[Route('/reservation/{id}', name: 'app_documents_reservation')]
    public function reservation(Reservation $reservation): Response
    {
        // Vérifier que l'utilisateur est propriétaire de la réservation
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette réservation.');
        }

        // Vérifier que la réservation est en attente de documents
        if ($reservation->getStatut() !== Reservation::STATUT_EN_ATTENTE_DOCUMENTS) {
            $this->addFlash('warning', 'Cette réservation ne nécessite pas de documents pour le moment.');
            return $this->redirectToRoute('app_documents_index');
        }

        // Récupérer les documents requis du package
        $documentsRequis = $reservation->getDepart()->getPackage()->getDocumentsRequis() ?? [];
        
        // Récupérer les documents fournis par tous les pèlerins
        $documentsFournis = [];
        foreach ($reservation->getPelerins() as $pelerin) {
            foreach ($pelerin->getDocuments() as $document) {
                $documentsFournis[$document->getType()] = true;
            }
        }

        return $this->render('documents/reservation.html.twig', [
            'reservation' => $reservation,
            'documentsRequis' => $documentsRequis,
            'documentsFournis' => $documentsFournis,
        ]);
    }

    #[Route('/upload/{pelerinId}', name: 'app_documents_upload', methods: ['POST'])]
    public function upload(
        Request $request,
        Pelerin $pelerin,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): Response {
        // Vérifier que l'utilisateur est propriétaire de la réservation
        if ($pelerin->getReservation()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette réservation.');
        }

        $uploadedFile = $request->files->get('document');
        $type = $request->request->get('type');
        $nom = $request->request->get('nom');

        if (!$uploadedFile || !$type || !$nom) {
            $this->addFlash('error', 'Tous les champs sont obligatoires.');
            return $this->redirectToRoute('app_documents_reservation', ['id' => $pelerin->getReservation()->getId()]);
        }

        // Vérifier le type de fichier
        $allowedTypes = ['pdf', 'jpg', 'jpeg', 'png'];
        $fileExtension = strtolower($uploadedFile->getClientOriginalExtension());
        
        if (!in_array($fileExtension, $allowedTypes)) {
            $this->addFlash('error', 'Type de fichier non autorisé. Utilisez PDF, JPG, JPEG ou PNG.');
            return $this->redirectToRoute('app_documents_reservation', ['id' => $pelerin->getReservation()->getId()]);
        }

        // Vérifier la taille du fichier (max 5MB)
        if ($uploadedFile->getSize() > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le fichier est trop volumineux. Taille maximum : 5MB.');
            return $this->redirectToRoute('app_documents_reservation', ['id' => $pelerin->getReservation()->getId()]);
        }

        // Générer un nom de fichier unique
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $fileName = $safeFilename . '-' . uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

        // Déplacer le fichier
        try {
            $uploadedFile->move(
                $this->getParameter('documents_directory'),
                $fileName
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du téléchargement du fichier.');
            return $this->redirectToRoute('app_documents_reservation', ['id' => $pelerin->getReservation()->getId()]);
        }

        // Créer l'entité Document
        $document = new Document();
        $document->setNom($nom);
        $document->setType($type);
        $document->setFichier($fileName);
        $document->setPelerin($pelerin);
        $document->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($document);
        $entityManager->flush();

        $this->addFlash('success', 'Document téléchargé avec succès !');

        return $this->redirectToRoute('app_documents_reservation', ['id' => $pelerin->getReservation()->getId()]);
    }

    #[Route('/delete/{id}', name: 'app_documents_delete', methods: ['POST'])]
    public function delete(Document $document, EntityManagerInterface $entityManager): Response
    {
        // Vérifier que l'utilisateur est propriétaire du document
        if ($document->getPelerin()->getReservation()->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à ce document.');
        }

        // Supprimer le fichier physique
        $filePath = $this->getParameter('documents_directory') . '/' . $document->getFichier();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $entityManager->remove($document);
        $entityManager->flush();

        $this->addFlash('success', 'Document supprimé avec succès.');

        return $this->redirectToRoute('app_documents_reservation', ['id' => $document->getPelerin()->getReservation()->getId()]);
    }
}
