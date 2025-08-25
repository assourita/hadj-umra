<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_admin_users_index')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_user_show')]
    public function show(User $user): Response
    {
        return $this->render('admin/users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setEmail($request->request->get('email'));
            $user->setPhone($request->request->get('phone'));
            $user->setPays($request->request->get('pays'));
            $user->setVerified((bool) $request->request->get('verified'));
            
            $roles = [];
            if ($request->request->get('role_admin')) {
                $roles[] = 'ROLE_ADMIN';
            }
            if ($request->request->get('role_user')) {
                $roles[] = 'ROLE_USER';
            }
            $user->setRoles($roles);

            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_users_index');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès !');
        return $this->redirectToRoute('app_admin_users_index');
    }

    #[Route('/{id}/toggle-status', name: 'app_admin_user_toggle_status', methods: ['POST'])]
    public function toggleStatus(User $user, EntityManagerInterface $entityManager): Response
    {
        $user->setVerified(!$user->isVerified());
        $entityManager->flush();

        $status = $user->isVerified() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Utilisateur {$status} avec succès !");
        
        return $this->redirectToRoute('app_admin_users_index');
    }
} 