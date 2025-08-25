<?php

namespace App\Controller\Admin;

use App\Entity\Depart;
use App\Entity\Package;
use App\Repository\DepartRepository;
use App\Repository\PackageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/departs')]
#[IsGranted('ROLE_ADMIN')]
class DepartController extends AbstractController
{
    #[Route('/', name: 'app_admin_departs_index')]
    public function index(DepartRepository $departRepository): Response
    {
        $departs = $departRepository->findAll();

        return $this->render('admin/departs/index.html.twig', [
            'departs' => $departs,
        ]);
    }

    #[Route('/new', name: 'app_admin_depart_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, PackageRepository $packageRepository): Response
    {
        if ($request->isMethod('POST')) {
            $package = $packageRepository->find($request->request->get('package_id'));
            
            if (!$package) {
                $this->addFlash('error', 'Package non trouvé !');
                return $this->redirectToRoute('app_admin_depart_new');
            }

            $depart = new Depart();
            $depart->setPackage($package);
            $depart->setVilleDepart($request->request->get('ville_depart') ?: 'Dakar');
            $depart->setDateDepart(new \DateTimeImmutable($request->request->get('date_depart')));
            $depart->setDateRetour(new \DateTimeImmutable($request->request->get('date_retour')));
            $depart->setQuotaTotal((int) $request->request->get('quota_total'));
            $depart->setQuotaVendu(0);
            
            // Gérer l'heure de départ
            $heureDepart = $request->request->get('heure_depart');
            if ($heureDepart) {
                $depart->setHeureDepart(new \DateTime($heureDepart));
            }

            // Gérer les informations de vol
            $depart->setCompagnieAerienne($request->request->get('compagnie_aerienne'));
            $depart->setNumeroVol($request->request->get('numero_vol'));

            // Gérer les remarques
            $depart->setRemarques($request->request->get('remarques'));

            // Gérer le statut actif
            $depart->setActive($request->request->has('is_active'));

            $entityManager->persist($depart);
            $entityManager->flush();

            // Gérer les tarifs (types de chambre)
            $tarifs = $request->request->all('tarifs');
            if (!empty($tarifs)) {
                foreach ($tarifs as $typeChambre => $tarifData) {
                    if (isset($tarifData['active']) && isset($tarifData['prix']) && !empty($tarifData['prix'])) {
                        $tarif = new \App\Entity\Tarif();
                        $tarif->setDepart($depart);
                        $tarif->setTypeChambre($typeChambre);
                        $tarif->setPrixBase($tarifData['prix']);
                        
                        // Gérer la réduction si elle est définie
                        if (isset($tarifData['reduction']) && !empty($tarifData['reduction'])) {
                            $tarif->setReduction($tarifData['reduction']);
                        }
                        
                        $tarif->setActive(true);
                        $entityManager->persist($tarif);
                    }
                }
                $entityManager->flush();
            }

            $this->addFlash('success', 'Départ créé avec succès !');
            return $this->redirectToRoute('app_admin_departs_index');
        }

        $packages = $packageRepository->findBy(['isActive' => true]);

        return $this->render('admin/departs/new.html.twig', [
            'packages' => $packages,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_depart_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Depart $depart, EntityManagerInterface $entityManager, PackageRepository $packageRepository): Response
    {
        if ($request->isMethod('POST')) {
            $package = $packageRepository->find($request->request->get('package_id'));
            
            if (!$package) {
                $this->addFlash('error', 'Package non trouvé !');
                return $this->redirectToRoute('app_admin_depart_edit', ['id' => $depart->getId()]);
            }

            $depart->setPackage($package);
            $depart->setVilleDepart($request->request->get('ville_depart') ?: 'Dakar');
            $depart->setDateDepart(new \DateTimeImmutable($request->request->get('date_depart')));
            $depart->setDateRetour(new \DateTimeImmutable($request->request->get('date_retour')));
            $depart->setQuotaTotal((int) $request->request->get('quota_total'));
            
            // Gérer l'heure de départ
            $heureDepart = $request->request->get('heure_depart');
            if ($heureDepart) {
                $depart->setHeureDepart(new \DateTime($heureDepart));
            } else {
                $depart->setHeureDepart(null);
            }

            // Gérer les informations de vol
            $depart->setCompagnieAerienne($request->request->get('compagnie_aerienne'));
            $depart->setNumeroVol($request->request->get('numero_vol'));

            // Gérer les remarques
            $depart->setRemarques($request->request->get('remarques'));

            // Gérer le statut actif
            $depart->setActive($request->request->has('is_active'));

            $entityManager->flush();

            // Gérer les tarifs (types de chambre)
            $tarifs = $request->request->all('tarifs');
            if (!empty($tarifs)) {
                // Créer un tableau des tarifs existants par type
                $existingTarifs = [];
                foreach ($depart->getTarifs() as $existingTarif) {
                    $existingTarifs[$existingTarif->getTypeChambre()] = $existingTarif;
                }

                foreach ($tarifs as $typeChambre => $tarifData) {
                    if (isset($tarifData['active']) && isset($tarifData['prix']) && !empty($tarifData['prix'])) {
                        // Mettre à jour le tarif existant ou créer un nouveau
                        if (isset($existingTarifs[$typeChambre])) {
                            $tarif = $existingTarifs[$typeChambre];
                        } else {
                            $tarif = new \App\Entity\Tarif();
                            $tarif->setDepart($depart);
                            $tarif->setTypeChambre($typeChambre);
                            $entityManager->persist($tarif);
                        }
                        
                        $tarif->setPrixBase($tarifData['prix']);
                        
                        // Gérer la réduction si elle est définie
                        if (isset($tarifData['reduction']) && !empty($tarifData['reduction'])) {
                            $tarif->setReduction($tarifData['reduction']);
                        } else {
                            $tarif->setReduction(null);
                        }
                        
                        $tarif->setActive(true);
                    } else {
                        // Désactiver le tarif s'il existe
                        if (isset($existingTarifs[$typeChambre])) {
                            $existingTarifs[$typeChambre]->setActive(false);
                        }
                    }
                }
                $entityManager->flush();
            }

            $this->addFlash('success', 'Départ mis à jour avec succès !');
            return $this->redirectToRoute('app_admin_departs_index');
        }

        $packages = $packageRepository->findBy(['isActive' => true]);

        return $this->render('admin/departs/edit.html.twig', [
            'depart' => $depart,
            'packages' => $packages,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_depart_delete', methods: ['POST'])]
    public function delete(Depart $depart, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($depart);
        $entityManager->flush();

        $this->addFlash('success', 'Départ supprimé avec succès !');
        return $this->redirectToRoute('app_admin_departs_index');
    }
} 