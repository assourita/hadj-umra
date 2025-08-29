<?php

namespace App\Command;

use App\Entity\ContactMessage;
use App\Entity\User;
use App\Repository\ContactMessageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-contact-message-user',
    description: 'Associe les messages de contact existants aux utilisateurs',
)]
class UpdateContactMessageUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ContactMessageRepository $contactMessageRepository,
        private UserRepository $userRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Association des messages de contact aux utilisateurs');

        // Récupérer tous les messages sans utilisateur associé
        $messages = $this->contactMessageRepository->findBy(['user' => null]);
        
        if (empty($messages)) {
            $io->success('Aucun message à traiter.');
            return Command::SUCCESS;
        }

        $io->text(sprintf('Traitement de %d messages...', count($messages)));

        $updated = 0;
        foreach ($messages as $message) {
            // Chercher l'utilisateur par email
            $user = $this->userRepository->findOneBy(['email' => $message->getEmail()]);
            
            if ($user) {
                $message->setUser($user);
                $updated++;
                $io->text(sprintf('✓ Message #%d associé à l\'utilisateur %s', $message->getId(), $user->getEmail()));
            } else {
                $io->text(sprintf('⚠ Message #%d : aucun utilisateur trouvé pour l\'email %s', $message->getId(), $message->getEmail()));
            }
        }

        if ($updated > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('%d messages ont été mis à jour.', $updated));
        } else {
            $io->warning('Aucun message n\'a pu être associé.');
        }

        return Command::SUCCESS;
    }
}
