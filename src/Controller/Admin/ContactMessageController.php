<?php

namespace App\Controller\Admin;

use App\Entity\ContactMessage;
use App\Repository\ContactMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/contact-messages')]
#[IsGranted('ROLE_ADMIN')]
class ContactMessageController extends AbstractController
{
    #[Route('/', name: 'app_admin_contact_messages_index')]
    public function index(ContactMessageRepository $contactMessageRepository): Response
    {
        $messages = $contactMessageRepository->findAllOrderedByDate();

        return $this->render('admin/contact_messages/index.html.twig', [
            'messages' => $messages,
        ]);
    }

    #[Route('/{id}/show', name: 'app_admin_contact_message_show')]
    public function show(ContactMessage $message): Response
    {
        return $this->render('admin/contact_messages/show.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/reply', name: 'app_admin_contact_message_reply', methods: ['GET', 'POST'])]
    public function reply(Request $request, ContactMessage $message, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $reponse = $request->request->get('reponse');
            $statut = $request->request->get('statut');

            $message->setReponse($reponse);
            $message->setStatut($statut);
            $message->setReponduAt(new \DateTimeImmutable());
            $message->setReponduPar($this->getUser()->getFullName());

            $entityManager->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès !');
            return $this->redirectToRoute('app_admin_contact_messages_index');
        }

        return $this->render('admin/contact_messages/reply.html.twig', [
            'message' => $message,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_contact_message_delete', methods: ['POST'])]
    public function delete(ContactMessage $message, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($message);
        $entityManager->flush();

        $this->addFlash('success', 'Message supprimé avec succès !');
        return $this->redirectToRoute('app_admin_contact_messages_index');
    }
}
