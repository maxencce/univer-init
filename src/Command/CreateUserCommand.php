<?php

namespace App\Command;

use App\Entity\Utilisateur;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Crée un nouvel utilisateur dans la base de données.'
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur')
            ->addArgument('nom', InputArgument::REQUIRED, 'Nom de l\'utilisateur')
            ->addArgument('password', InputArgument::REQUIRED, 'Mot de passe')
            ->addArgument('role', InputArgument::REQUIRED, 'Rôle (ADMIN, GESEC, ADHERENT)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $input->getArgument('email');
        $nom = $input->getArgument('nom');
        $password = $input->getArgument('password');
        $role = strtoupper($input->getArgument('role'));

        if (!in_array($role, array_column(UserRole::cases(), 'value'))) {
            $output->writeln('<error>Rôle invalide. Utilisez ADMIN, GESEC ou ADHERENT.</error>');
            return Command::FAILURE;
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setEmail($email);
        $utilisateur->setNom($nom);
        $utilisateur->setRole(UserRole::from($role));
        $utilisateur->setMotDePasse(
            $this->passwordHasher->hashPassword($utilisateur, $password)
        );
        $utilisateur->setCreatedAt(new \DateTimeImmutable());
        $utilisateur->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($utilisateur);
        $this->entityManager->flush();

        $output->writeln('<info>Utilisateur créé avec succès !</info>');
        return Command::SUCCESS;
    }
}
