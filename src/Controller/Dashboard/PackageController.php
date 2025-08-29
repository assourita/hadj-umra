<?php

namespace App\Controller\Dashboard;

use App\Entity\Package;
use App\Form\PackageType;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/dashboard/package')]
class PackageController extends AbstractController
{
    #[Route('/', name: 'dashboard_package_index', methods: ['GET'])]
    public function index(PackageRepository $packageRepository): Response
    {
        return $this->render('dashboard/package/index.html.twig', [
            'packages' => $packageRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'dashboard_package_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $package = new Package();
        $form = $this->createForm(PackageType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('packages_directory'),
                        $newFilename
                    );
                    $package->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $package->setCreatedAt(new \DateTime());
            $package->setUpdatedAt(new \DateTime());

            $entityManager->persist($package);
            $entityManager->flush();

            $this->addFlash('success', 'Package créé avec succès !');
            return $this->redirectToRoute('dashboard_package_index');
        }

        return $this->render('dashboard/package/new.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dashboard_package_show', methods: ['GET'])]
    public function show(Package $package): Response
    {
        return $this->render('dashboard/package/show.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/{id}/edit', name: 'dashboard_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PackageType::class, $package);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gérer l'upload d'image si nécessaire
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('packages_directory'),
                        $newFilename
                    );
                    $package->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                }
            }

            $package->setUpdatedAt(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'Package modifié avec succès !');
            return $this->redirectToRoute('dashboard_package_index');
        }

        return $this->render('dashboard/package/edit.html.twig', [
            'package' => $package,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'dashboard_package_delete', methods: ['POST'])]
    public function delete(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$package->getId(), $request->request->get('_token'))) {
            $entityManager->remove($package);
            $entityManager->flush();

            $this->addFlash('success', 'Package supprimé avec succès !');
        }

        return $this->redirectToRoute('dashboard_package_index');
    }

    #[Route('/{id}/toggle-status', name: 'dashboard_package_toggle_status', methods: ['POST'])]
    public function toggleStatus(Package $package, EntityManagerInterface $entityManager): Response
    {
        $package->setIsActive(!$package->isIsActive());
        $package->setUpdatedAt(new \DateTime());
        
        $entityManager->flush();

        $status = $package->isIsActive() ? 'activé' : 'désactivé';
        $this->addFlash('success', "Package {$status} avec succès !");

        return $this->redirectToRoute('dashboard_package_index');
    }
}
