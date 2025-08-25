<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Créer un utilisateur administrateur',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email de l\'administrateur')
            ->addArgument('password', InputArgument::OPTIONAL, 'Mot de passe')
            ->addOption('super-admin', 's', InputOption::VALUE_NONE, 'Créer un super administrateur')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Rôle à assigner', 'ROLE_ADMIN')
            ->setHelp('Cette commande permet de créer un utilisateur administrateur rapidement.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Récupérer ou demander l'email
        $email = $input->getArgument('email');
        if (!$email) {
            $email = $io->ask('Email de l\'administrateur');
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error("Un utilisateur avec l'email '$email' existe déjà.");
            return Command::FAILURE;
        }

        // Récupérer ou demander le mot de passe
        $password = $input->getArgument('password');
        if (!$password) {
            $password = $io->askHidden('Mot de passe (8 caractères minimum)');
        }

        if (strlen($password) < 8) {
            $io->error('Le mot de passe doit contenir au moins 8 caractères.');
            return Command::FAILURE;
        }

        // Déterminer le rôle
        $role = $input->getOption('super-admin') ? 'ROLE_SUPER_ADMIN' : $input->getOption('role');

        // Demander les informations supplémentaires
        $nom = $io->ask('Nom de famille');
        $prenom = $io->ask('Prénom');
        $phone = $io->ask('Téléphone (optionnel)', '');
        $pays = $io->ask('Pays', 'Mali');

        // Créer l'utilisateur
        $user = new User();
        $user->setEmail($email);
        $user->setNom($nom);
        $user->setPrenom($prenom);
        $user->setPhone($phone);
        $user->setPays($pays);
        $user->setRoles([$role]);
        $user->setVerified(true);
        
        // Hasher le mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Sauvegarder
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success([
            'Utilisateur administrateur créé avec succès !',
            "Email: $email",
            "Rôle: $role",
            "Nom: $prenom $nom"
        ]);

        return Command::SUCCESS;
    }
} 