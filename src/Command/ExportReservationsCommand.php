<?php

namespace App\Command;

use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export:reservations',
    description: 'Exporter les réservations au format CSV ou Excel',
)]
class ExportReservationsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ReservationRepository $reservationRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Format d\'export (csv|excel)', 'csv')
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL, 'Filtrer par statut')
            ->addOption('from-date', null, InputOption::VALUE_OPTIONAL, 'Date de début (Y-m-d)')
            ->addOption('to-date', null, InputOption::VALUE_OPTIONAL, 'Date de fin (Y-m-d)')
            ->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Fichier de sortie', 'exports/reservations_' . date('Y-m-d_H-i-s') . '.csv')
            ->setHelp('Cette commande exporte les réservations selon les critères spécifiés.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $format = $input->getOption('format');
        $status = $input->getOption('status');
        $fromDate = $input->getOption('from-date');
        $toDate = $input->getOption('to-date');
        $outputFile = $input->getOption('output');

        // Valider le format
        if (!in_array($format, ['csv', 'excel'])) {
            $io->error('Format non supporté. Utilisez csv ou excel.');
            return Command::FAILURE;
        }

        // Construire les critères de recherche
        $criteria = [];
        if ($status) {
            $criteria['statut'] = $status;
        }

        $io->info('Récupération des réservations...');

        // Récupérer les réservations
        $queryBuilder = $this->entityManager->createQueryBuilder();
        $queryBuilder
            ->select('r', 'u', 'd', 'p', 'pel', 'pay')
            ->from('App\Entity\Reservation', 'r')
            ->leftJoin('r.user', 'u')
            ->leftJoin('r.depart', 'd')
            ->leftJoin('d.package', 'p')
            ->leftJoin('r.pelerins', 'pel')
            ->leftJoin('r.paiements', 'pay')
        ;

        if ($status) {
            $queryBuilder->andWhere('r.statut = :status')
                        ->setParameter('status', $status);
        }

        if ($fromDate) {
            $queryBuilder->andWhere('r.createdAt >= :fromDate')
                        ->setParameter('fromDate', new \DateTime($fromDate));
        }

        if ($toDate) {
            $queryBuilder->andWhere('r.createdAt <= :toDate')
                        ->setParameter('toDate', new \DateTime($toDate . ' 23:59:59'));
        }

        $reservations = $queryBuilder->getQuery()->getResult();

        if (empty($reservations)) {
            $io->warning('Aucune réservation trouvée avec ces critères.');
            return Command::SUCCESS;
        }

        // Créer le répertoire de sortie si nécessaire
        $outputDir = dirname($outputFile);
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $io->info("Export de " . count($reservations) . " réservations...");

        // Exporter selon le format
        if ($format === 'csv') {
            $this->exportToCsv($reservations, $outputFile);
        } else {
            $this->exportToExcel($reservations, $outputFile);
        }

        $io->success([
            'Export terminé avec succès !',
            "Fichier: $outputFile",
            'Réservations exportées: ' . count($reservations)
        ]);

        return Command::SUCCESS;
    }

    private function exportToCsv(array $reservations, string $outputFile): void
    {
        $handle = fopen($outputFile, 'w');
        
        // En-têtes CSV
        $headers = [
            'Code Dossier',
            'Statut',
            'Client Email',
            'Client Nom',
            'Client Prénom',
            'Package',
            'Ville Départ',
            'Date Départ',
            'Date Retour',
            'Type Chambre',
            'Nb Pèlerins',
            'Total (XOF)',
            'Acompte (XOF)',
            'Reste (XOF)',
            'Total Payé (XOF)',
            'Date Création',
            'Date Limite Documents',
            'Remarques'
        ];
        
        fputcsv($handle, $headers);

        foreach ($reservations as $reservation) {
            $row = [
                $reservation->getCodeDossier(),
                $reservation->getStatut(),
                $reservation->getUser()->getEmail(),
                $reservation->getUser()->getNom(),
                $reservation->getUser()->getPrenom(),
                $reservation->getDepart()->getPackage()->getTitre(),
                $reservation->getDepart()->getVilleDepart(),
                $reservation->getDepart()->getDateDepart()->format('Y-m-d'),
                $reservation->getDepart()->getDateRetour()->format('Y-m-d'),
                $reservation->getTypeChambre(),
                $reservation->getNbPelerins(),
                $reservation->getTotal(),
                $reservation->getAcompte(),
                $reservation->getReste(),
                $reservation->getTotalPaye(),
                $reservation->getCreatedAt()->format('Y-m-d H:i:s'),
                $reservation->getDateLimiteDocument() ? $reservation->getDateLimiteDocument()->format('Y-m-d') : '',
                $reservation->getRemarques() ?? ''
            ];
            
            fputcsv($handle, $row);
        }

        fclose($handle);
    }

    private function exportToExcel(array $reservations, string $outputFile): void
    {
        // Pour Excel, nous utiliserons un format CSV avec un nom .xlsx
        // En production, on pourrait utiliser PhpSpreadsheet
        $csvFile = str_replace('.xlsx', '.csv', $outputFile);
        $this->exportToCsv($reservations, $csvFile);
        
        // Renommer le fichier pour indiquer qu'il s'agit d'Excel
        if (file_exists($csvFile)) {
            rename($csvFile, $outputFile);
        }
    }
} 