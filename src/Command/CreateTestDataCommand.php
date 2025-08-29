<?php

namespace App\Command;

use App\Entity\Package;
use App\Entity\Announcement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-test-data',
    description: 'Créer des données de test pour les packages et annonces',
)]
class CreateTestDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Vérifier si des packages existent déjà
        $existingPackages = $this->entityManager->getRepository(Package::class)->findAll();
        if (empty($existingPackages)) {
            // Créer le package Hajj
            $hajjPackage = new Package();
            $hajjPackage->setName('Package Hajj Premium');
            $hajjPackage->setDescription('Package complet pour le Hajj incluant vol, hôtel 5 étoiles, transport et guide. Ce package comprend tous les services nécessaires pour un pèlerinage confortable et spirituel.');
            $hajjPackage->setPrice(3500.00);
            $hajjPackage->setType('hajj');
            $hajjPackage->setFeatures([
                'Vol aller-retour en classe économique',
                'Hôtel 5 étoiles à Makkah et Madinah',
                'Transport VIP entre les sites sacrés',
                'Guide francophone expérimenté',
                'Assurance voyage complète',
                'Visa et documents administratifs',
                'Repas halal inclus',
                'Support 24/7'
            ]);
            $hajjPackage->setIsActive(true);
            $hajjPackage->setCreatedAt(new \DateTime());
            $hajjPackage->setUpdatedAt(new \DateTime());

            // Créer le package Umra
            $umraPackage = new Package();
            $umraPackage->setName('Package Umra Économique');
            $umraPackage->setDescription('Package Umra abordable avec tous les services essentiels. Idéal pour les familles et les groupes souhaitant effectuer leur Umra dans les meilleures conditions.');
            $umraPackage->setPrice(1800.00);
            $umraPackage->setType('umra');
            $umraPackage->setFeatures([
                'Vol aller-retour en classe économique',
                'Hôtel 3 étoiles à Makkah et Madinah',
                'Transport en groupe',
                'Guide francophone',
                'Assurance voyage de base',
                'Visa et documents administratifs',
                'Petit-déjeuner inclus',
                'Support téléphonique'
            ]);
            $umraPackage->setIsActive(true);
            $umraPackage->setCreatedAt(new \DateTime());
            $umraPackage->setUpdatedAt(new \DateTime());

            // Créer le package VIP
            $vipPackage = new Package();
            $vipPackage->setName('Package VIP Luxe');
            $vipPackage->setDescription('Package VIP haut de gamme pour Hajj et Umra avec services premium et confort maximal. Pour ceux qui recherchent l\'excellence dans leur voyage spirituel.');
            $vipPackage->setPrice(5500.00);
            $vipPackage->setType('vip');
            $vipPackage->setFeatures([
                'Vol aller-retour en classe affaires',
                'Hôtel 5 étoiles luxe à Makkah et Madinah',
                'Transport privé avec chauffeur',
                'Guide privé francophone',
                'Assurance voyage premium',
                'Visa express et services VIP',
                'Repas gastronomiques inclus',
                'Suite privée avec vue sur la Kaaba',
                'Services de conciergerie 24/7',
                'Accompagnement personnalisé'
            ]);
            $vipPackage->setIsActive(true);
            $vipPackage->setCreatedAt(new \DateTime());
            $vipPackage->setUpdatedAt(new \DateTime());

            // Persister les packages
            $this->entityManager->persist($hajjPackage);
            $this->entityManager->persist($umraPackage);
            $this->entityManager->persist($vipPackage);
            
            $io->success('3 packages de test ont été créés avec succès !');
        } else {
            $io->info('Des packages existent déjà dans la base de données.');
        }

        // Vérifier si des annonces existent déjà
        $existingAnnouncements = $this->entityManager->getRepository(Announcement::class)->findAll();
        if (empty($existingAnnouncements)) {
            // Créer une annonce urgente
            $urgentAnnouncement = new Announcement();
            $urgentAnnouncement->setTitle('⚠️ URGENT : Dernières places pour Hajj 2024');
            $urgentAnnouncement->setContent('<p><strong>Attention !</strong> Il ne reste que quelques places pour le Hajj 2024.</p><p>Nos packages premium incluent :</p><ul><li>Vol aller-retour en classe économique</li><li>Hébergement 5 étoiles à Makkah et Madinah</li><li>Transport VIP entre les sites sacrés</li><li>Guide francophone expérimenté</li><li>Assurance voyage complète</li></ul><p><em>Contactez-nous rapidement pour réserver votre place !</em></p>');
            $urgentAnnouncement->setType('urgent');
            $urgentAnnouncement->setPriority(9);
            $urgentAnnouncement->setIsPublished(true);
            $urgentAnnouncement->setPublishedAt(new \DateTime('-2 days'));

            // Créer une annonce promotionnelle
            $promoAnnouncement = new Announcement();
            $promoAnnouncement->setTitle('🎉 Promotion spéciale Umra Ramadan 2024');
            $promoAnnouncement->setContent('<p>Profitez de nos <strong>tarifs réduits</strong> pour l\'Umra pendant le mois de Ramadan !</p><p>Offre limitée : <span style="color: #e74c3c; font-weight: bold;">-15% sur tous nos packages Umra</span></p><p>Cette promotion inclut :</p><ul><li>Réduction de 15% sur le prix total</li><li>Hébergement confortable près de la Masjid al-Haram</li><li>Transport organisé</li><li>Support 24/7</li></ul><p>Réservez maintenant et économisez !</p>');
            $promoAnnouncement->setType('promotion');
            $promoAnnouncement->setPriority(7);
            $promoAnnouncement->setIsPublished(true);
            $promoAnnouncement->setPublishedAt(new \DateTime('-5 days'));

            // Créer une annonce d'information
            $infoAnnouncement = new Announcement();
            $infoAnnouncement->setTitle('📋 Guide du pèlerin : Conseils pour votre voyage');
            $infoAnnouncement->setContent('<p>Découvrez notre guide complet pour préparer votre pèlerinage en toute sérénité.</p><h4>Conseils importants :</h4><ul><li><strong>Documents requis :</strong> Passeport valide, visa, certificats de vaccination</li><li><strong>Vêtements :</strong> Privilégiez des vêtements confortables et appropriés</li><li><strong>Santé :</strong> Consultez votre médecin avant le départ</li><li><strong>Préparation spirituelle :</strong> Étudiez les rituels du Hajj/Umra</li></ul><p>Notre équipe est là pour vous accompagner à chaque étape de votre voyage spirituel.</p>');
            $infoAnnouncement->setType('information');
            $infoAnnouncement->setPriority(5);
            $infoAnnouncement->setIsPublished(true);
            $infoAnnouncement->setPublishedAt(new \DateTime('-1 week'));

            // Persister les annonces
            $this->entityManager->persist($urgentAnnouncement);
            $this->entityManager->persist($promoAnnouncement);
            $this->entityManager->persist($infoAnnouncement);
            
            $io->success('3 annonces de test ont été créées avec succès !');
        } else {
            $io->info('Des annonces existent déjà dans la base de données.');
        }

        $this->entityManager->flush();

        $io->table(
            ['Type', 'Nombre créé'],
            [
                ['Packages', count($existingPackages) == 0 ? '3' : '0'],
                ['Annonces', count($existingAnnouncements) == 0 ? '3' : '0']
            ]
        );

        return Command::SUCCESS;
    }
}
