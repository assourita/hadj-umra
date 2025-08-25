<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_client_dashboard');
        }

        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setEmail($request->request->get('email'));
            $user->setNom($request->request->get('nom'));
            $user->setPrenom($request->request->get('prenom'));
            $user->setPhone($request->request->get('phone'));
            $user->setPays($request->request->get('pays'));

            $password = $request->request->get('password');
            $confirmPassword = $request->request->get('confirm_password');

            // Validation du mot de passe
            if ($password !== $confirmPassword) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->render('registration/register.html.twig', [
                    'user' => $user
                ]);
            }

            if (strlen($password) < 6) {
                $this->addFlash('error', 'Le mot de passe doit faire au moins 6 caractères.');
                return $this->render('registration/register.html.twig', [
                    'user' => $user
                ]);
            }

            // Encoder le mot de passe
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $password
                )
            );

            // Validation de l'entité
            $errors = $validator->validate($user);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->render('registration/register.html.twig', [
                    'user' => $user
                ]);
            }

            try {
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                return $this->redirectToRoute('app_login');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte.');
            }
        }

        return $this->render('registration/register.html.twig', [
            'user' => new User()
        ]);
    }
} 