<?php

namespace App\Controller\Dashboard;

use App\Entity\Contact;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/dashboard/contact")
 */
class ContactController extends AbstractController
{
    /**
     * @Route("/", name="dashboard_contact_index", methods={"GET"})
     */
    public function index(ContactRepository $contactRepository): Response
    {
        return $this->render('dashboard/contact/index.html.twig', [
            'contacts' => $contactRepository->findAll(),
            'unreadCount' => $contactRepository->countUnreadMessages(),
        ]);
    }

    /**
     * @Route("/{id}", name="dashboard_contact_show", methods={"GET"})
     */
    public function show(Contact $contact, EntityManagerInterface $entityManager): Response
    {
        // Marquer comme lu
        if (!$contact->getIsRead()) {
            $contact->setIsRead(true);
            $entityManager->flush();
        }

        return $this->render('dashboard/contact/show.html.twig', [
            'contact' => $contact,
        ]);
    }

    /**
     * @Route("/{id}/toggle-read", name="dashboard_contact_toggle_read", methods={"POST"})
     */
    public function toggleRead(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('toggle_read'.$contact->getId(), $request->request->get('_token'))) {
            $contact->setIsRead(!$contact->getIsRead());
            $entityManager->flush();
            
            $status = $contact->getIsRead() ? 'lu' : 'non lu';
            $this->addFlash('success', "Message marqué comme {$status}");
        }

        return $this->redirectToRoute('dashboard_contact_index');
    }

    /**
     * @Route("/{id}", name="dashboard_contact_delete", methods={"POST"})
     */
    public function delete(Request $request, Contact $contact, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contact->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contact);
            $entityManager->flush();
            $this->addFlash('success', 'Message supprimé avec succès');
        }

        return $this->redirectToRoute('dashboard_contact_index');
    }

    /**
     * @Route("/mark-all-read", name="dashboard_contact_mark_all_read", methods={"POST"})
     */
    public function markAllAsRead(Request $request, ContactRepository $contactRepository, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('mark_all_read', $request->request->get('_token'))) {
            $unreadMessages = $contactRepository->findUnreadMessages();
            
            foreach ($unreadMessages as $message) {
                $message->setIsRead(true);
            }
            
            $entityManager->flush();
            $this->addFlash('success', 'Tous les messages ont été marqués comme lus');
        }

        return $this->redirectToRoute('dashboard_contact_index');
    }
}
