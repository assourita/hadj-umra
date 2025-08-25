<?php

namespace App\Controller\Admin;

use App\Entity\Package;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Form\PackageType;

#[Route('/admin/packages')]
#[IsGranted('ROLE_ADMIN')]
class PackageController extends AbstractController
{
    #[Route('/', name: 'app_admin_packages_index')]
    public function index(PackageRepository $packageRepository): Response
    {
        $packages = $packageRepository->findAll();

        return $this->render('admin/packages/index.html.twig', [
            'packages' => $packages,
        ]);
    }

    #[Route('/new', name: 'app_admin_package_new', methods: ['GET', 'POST'])]
    public function nouveau(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $package = new Package();
        $form = $this->createForm(PackageType::class, $package);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter les documents requis
            $documentsRequis = $form->get('documentsRequis')->getData();
            if ($documentsRequis) {
                $package->setDocumentsRequis($documentsRequis);
            }
            
            // Générer le slug automatiquement
            $package->computeSlug($slugger);

            $entityManager->persist($package);
            $entityManager->flush();

            $this->addFlash('success', 'Package créé avec succès !');
            return $this->redirectToRoute('app_admin_packages_index');
        }

        return $this->render('admin/packages/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PackageType::class, $package);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Traiter les documents requis
            $documentsRequis = $form->get('documentsRequis')->getData();
            if ($documentsRequis) {
                $package->setDocumentsRequis($documentsRequis);
            }
            
            // Mettre à jour le slug si le titre a changé
            $package->computeSlug($slugger);

            $entityManager->flush();

            $this->addFlash('success', 'Package mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_packages_index');
        }

        return $this->render('admin/packages/edit.html.twig', [
            'form' => $form->createView(),
            'package' => $package,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_package_delete', methods: ['POST'])]
    public function delete(Package $package, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($package);
        $entityManager->flush();

        $this->addFlash('success', 'Package supprimé avec succès !');
        return $this->redirectToRoute('app_admin_packages_index');
    }
} 