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
        if ($request->isMethod('POST')) {
            // Validation des champs obligatoires
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));
            $dureeJours = $request->request->get('dureeJours');
            
            if (empty($titre) || empty($description) || empty($dureeJours)) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires (Titre, Description, Durée)');
                return $this->render('admin/packages/new.html.twig');
            }
            
            $package = new Package();
            
            // Récupérer les données du formulaire avec validation
            $package->setTitre($titre);
            $package->setDescription($description);
            $package->setProgramme(trim($request->request->get('programme', '')) ?: null);
            $package->setPrixBase(trim($request->request->get('prixBase', '')) ?: null);
            $package->setDevise($request->request->get('devise', 'XOF'));
            $package->setDureeJours((int) $dureeJours);
            $package->setHotelMakkah(trim($request->request->get('hotelMakkah', '')) ?: null);
            $package->setHotelMadinah(trim($request->request->get('hotelMadinah', '')) ?: null);
            $package->setInclus(trim($request->request->get('inclus', '')) ?: null);
            $package->setNonInclus(trim($request->request->get('nonInclus', '')) ?: null);
            $package->setActive($request->request->get('isActive') === 'on');
            
            // Les documents requis sont automatiquement configurés dans le constructeur
            // Pas besoin de les modifier ici
            
            // Traiter les images
            $uploadedFiles = $request->files->get('images');
            $imageNames = [];
            
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalName);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                        
                        try {
                            $file->move(
                                $this->getParameter('packages_directory'),
                                $newFilename
                            );
                            $imageNames[] = '/uploads/packages/' . $newFilename;
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                        }
                    }
                }
            }
            
            // Si aucune image n'a été uploadée, utiliser des images par défaut
            if (empty($imageNames)) {
                $imageNames = [
                    '/uploads/packages/omra1.webp',
                    '/uploads/packages/omra2.webp',
                    '/uploads/packages/omra3.webp'
                ];
            }
            
            $package->setImages($imageNames);
            
            // Générer le slug
            $package->computeSlug($slugger);
            
            $entityManager->persist($package);
            $entityManager->flush();
            
            $this->addFlash('success', 'Package créé avec succès !');
            return $this->redirectToRoute('app_admin_packages_index');
        }

        return $this->render('admin/packages/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'app_admin_package_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Package $package, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($request->isMethod('POST')) {
            // Validation des champs obligatoires
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));
            $dureeJours = $request->request->get('dureeJours');
            
            if (empty($titre) || empty($description) || empty($dureeJours)) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires (Titre, Description, Durée)');
                return $this->render('admin/packages/edit.html.twig', [
                    'package' => $package,
                ]);
            }
            
            // Mettre à jour les données
            $package->setTitre($titre);
            $package->setDescription($description);
            $package->setProgramme(trim($request->request->get('programme', '')) ?: null);
            $package->setPrixBase(trim($request->request->get('prixBase', '')) ?: null);
            $package->setDevise($request->request->get('devise', 'XOF'));
            $package->setDureeJours((int) $dureeJours);
            $package->setHotelMakkah(trim($request->request->get('hotelMakkah', '')) ?: null);
            $package->setHotelMadinah(trim($request->request->get('hotelMadinah', '')) ?: null);
            $package->setInclus(trim($request->request->get('inclus', '')) ?: null);
            $package->setNonInclus(trim($request->request->get('nonInclus', '')) ?: null);
            $package->setActive($request->request->get('isActive') === 'on');
            
            // Traiter les nouvelles images
            $uploadedFiles = $request->files->get('images');
            if ($uploadedFiles) {
                foreach ($uploadedFiles as $file) {
                    if ($file instanceof UploadedFile && $file->isValid()) {
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $safeFilename = $slugger->slug($originalName);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();
                        
                        try {
                            $file->move(
                                $this->getParameter('packages_directory'),
                                $newFilename
                            );
                            $currentImages = $package->getImages() ?? [];
                            $currentImages[] = '/uploads/packages/' . $newFilename;
                            $package->setImages($currentImages);
                        } catch (\Exception $e) {
                            $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                        }
                    }
                }
            }
            
            $package->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();
            
            $this->addFlash('success', 'Package modifié avec succès !');
            return $this->redirectToRoute('app_admin_packages_index');
        }

        return $this->render('admin/packages/edit.html.twig', [
            'package' => $package,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_package_delete', methods: ['POST'])]
    public function delete(Request $request, Package $package, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$package->getId(), $request->request->get('_token'))) {
            $entityManager->remove($package);
            $entityManager->flush();
            
            $this->addFlash('success', 'Package supprimé avec succès !');
        }

        return $this->redirectToRoute('app_admin_packages_index');
    }
} 