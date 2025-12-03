<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\UserStatus;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// to use this command, run: php bin/console app:create-admin
// This command will create an admin user with the email and password defined in .env
// If the admin user already exists, it will not create a new one and will inform you.
// Make sure to set the environment variables DEFAULT_ADMIN_EMAIL and DEFAULT_ADMIN_PASSWORD in your .env file.

#[AsCommand(
    name: 'app:create-admin',
    description: 'Create the default admin user if it does not already exist'
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface      $em,
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = $_ENV['DEFAULT_ADMIN_EMAIL'] ?? '1@1.1';
        $plainPassword = $_ENV['DEFAULT_ADMIN_PASSWORD'] ?? 'Admin123';

        $repo = $this->em->getRepository(User::class);

        // check if admin already exists
        $existing = $repo->findOneBy(['email' => $email]);
        if ($existing) {
            $output->writeln('<info>Admin already exists with email: ' . $email . '</info>');
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setRole(UserRole::ADMIN);
        $user->setStatus(UserStatus::ACTIVE);
        $user->setFirstName('Default');
        $user->setLastName('Admin');
        $user->setMatricule('ADMIN001');
        $user->setDepartment('Administration');
        $user->setHireDate(new \DateTime());
        $user->setJobTitle('Administrator');
        $user->setbirthday(new \DateTime('1990-01-01'));

// or ['ROLE_ADMIN', 'ROLE_USER'] depending on your application

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>Admin created successfully!</info>');
        $output->writeln('<comment>Email: ' . $email . '</comment>');
        $output->writeln('<comment>Password: ' . $plainPassword . '</comment>');

        return Command::SUCCESS;
    }
}