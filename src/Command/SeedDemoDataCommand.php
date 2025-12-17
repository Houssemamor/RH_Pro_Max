<?php

namespace App\Command;

use App\Entity\JobOffer;
use App\Entity\JobOfferSkill;
use App\Entity\Skill;
use App\Entity\SkillCategory;
use App\Entity\User;
use App\Enum\JobOfferStatus;
use App\Enum\SkillLevel;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Seeds a small, deterministic set of demo data for local development.
 *
 * Responsibilities:
 * - Ensure a creator user exists (HR manager)
 * - Ensure at least one SkillCategory exists
 * - Insert 5 Skills and 5 JobOffers if missing
 *
 * Design choice:
 * - Console Command pattern (Symfony): a single entry point for seeding.
 * - Idempotent by natural keys (email, category name, skill name, offer title).
 */
#[AsCommand(
    name: 'app:seed-demo-data',
    description: 'Seed 5 skills and 5 job offers into the database (idempotent)'
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $creator = $this->getOrCreateCreatorUser($output);
        $category = $this->getOrCreateSkillCategory($output);

        $skills = $this->seedSkills($category, $output);
        $this->seedJobOffers($creator, $skills, $output);

        $this->entityManager->flush();

        $output->writeln('<info>Done.</info>');

        return Command::SUCCESS;
    }

    private function getOrCreateCreatorUser(OutputInterface $output): User
    {
        $userRepository = $this->entityManager->getRepository(User::class);

        // Prefer a deterministic "seed" user. If not present, fall back to any existing user.
        $seedEmail = $_ENV['SEED_HR_EMAIL'] ?? 'seed.hr@example.com';
        $plainPassword = $_ENV['SEED_HR_PASSWORD'] ?? 'HrManager123';

        /** @var User|null $existingSeed */
        $existingSeed = $userRepository->findOneBy(['email' => $seedEmail]);
        if ($existingSeed instanceof User) {
            $output->writeln('<comment>Using existing creator user: ' . $seedEmail . '</comment>');
            return $existingSeed;
        }

        /** @var User|null $anyExisting */
        $anyExisting = $userRepository->findOneBy([], ['id' => 'ASC']);
        if ($anyExisting instanceof User) {
            $output->writeln('<comment>No seed user found; using first existing user: ' . $anyExisting->getEmail() . '</comment>');
            return $anyExisting;
        }

        // Create a minimal HR user so JobOffer.creator (NOT NULL) can be satisfied.
        $user = new User();
        $user->setEmail($seedEmail);
        $user->setRole(UserRole::HR_MANAGER);
        $user->setStatus(UserStatus::ACTIVE);
        $user->setFirstName('Seed');
        $user->setLastName('HR');
        $user->setDepartment('HR');
        $user->setJobTitle('HR Manager');
        $user->setMatricule('SEED-HR-001');
        $user->setHireDate(new \DateTime());
        $user->setBirthday(new \DateTime('1990-01-01'));

        $hashed = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashed);

        $this->entityManager->persist($user);
        $output->writeln('<info>Created seed creator user: ' . $seedEmail . '</info>');

        return $user;
    }

    private function getOrCreateSkillCategory(OutputInterface $output): SkillCategory
    {
        $categoryRepository = $this->entityManager->getRepository(SkillCategory::class);

        $categoryName = 'Technical';

        /** @var SkillCategory|null $existing */
        $existing = $categoryRepository->findOneBy(['name' => $categoryName]);
        if ($existing instanceof SkillCategory) {
            $output->writeln('<comment>Using existing skill category: ' . $categoryName . '</comment>');
            return $existing;
        }

        $category = new SkillCategory();
        $category->setName($categoryName);
        $category->setDescription('Technical skills for job offers and profiles');

        $this->entityManager->persist($category);
        $output->writeln('<info>Created skill category: ' . $categoryName . '</info>');

        return $category;
    }

    /**
     * @return array<string, Skill> keyed by skill name
     */
    private function seedSkills(SkillCategory $category, OutputInterface $output): array
    {
        $skillRepository = $this->entityManager->getRepository(Skill::class);

        $seedSkills = [
            ['name' => 'PHP', 'description' => 'Server-side development with PHP 8+'],
            ['name' => 'Symfony', 'description' => 'Symfony framework (controllers, forms, security)'],
            ['name' => 'Doctrine ORM', 'description' => 'Entity mapping and database access with Doctrine'],
            ['name' => 'SQL', 'description' => 'Writing queries and understanding relational models'],
            ['name' => 'Docker', 'description' => 'Containerized development and deployment basics'],
        ];

        $result = [];
        $createdCount = 0;

        foreach ($seedSkills as $seedSkill) {
            $name = $seedSkill['name'];

            /** @var Skill|null $existing */
            $existing = $skillRepository->findOneBy(['name' => $name]);
            if ($existing instanceof Skill) {
                $result[$name] = $existing;
                continue;
            }

            $skill = new Skill();
            $skill->setName($name);
            $skill->setDescription($seedSkill['description']);
            $skill->setCategory($category);

            $this->entityManager->persist($skill);
            $result[$name] = $skill;
            $createdCount++;
        }

        $output->writeln('<info>Skills: created ' . $createdCount . ' / 5</info>');

        return $result;
    }

    /**
     * @param array<string, Skill> $skillsByName
     */
    private function seedJobOffers(User $creator, array $skillsByName, OutputInterface $output): void
    {
        $offerRepository = $this->entityManager->getRepository(JobOffer::class);

        $now = new \DateTimeImmutable();

        $seedOffers = [
            [
                'title' => 'Symfony Backend Developer',
                'location' => 'Casablanca',
                'description' => 'Build and maintain Symfony APIs and back-office features. Focus on clean architecture and testing.',
                'closingDate' => $now->modify('+30 days'),
                'skills' => [
                    ['name' => 'Symfony', 'level' => SkillLevel::ADVANCED, 'required' => true],
                    ['name' => 'Doctrine ORM', 'level' => SkillLevel::INTERMEDIATE, 'required' => true],
                    ['name' => 'SQL', 'level' => SkillLevel::INTERMEDIATE, 'required' => false],
                ],
            ],
            [
                'title' => 'PHP Developer (Internal Tools)',
                'location' => 'Rabat',
                'description' => 'Develop internal HR tools in PHP. Work closely with HR stakeholders and iterate quickly.',
                'closingDate' => $now->modify('+21 days'),
                'skills' => [
                    ['name' => 'PHP', 'level' => SkillLevel::ADVANCED, 'required' => true],
                    ['name' => 'SQL', 'level' => SkillLevel::INTERMEDIATE, 'required' => true],
                ],
            ],
            [
                'title' => 'DevOps-minded Web Developer',
                'location' => 'Remote',
                'description' => 'Support the team with developer experience improvements and Docker-based environments.',
                'closingDate' => $now->modify('+45 days'),
                'skills' => [
                    ['name' => 'Docker', 'level' => SkillLevel::INTERMEDIATE, 'required' => true],
                    ['name' => 'PHP', 'level' => SkillLevel::INTERMEDIATE, 'required' => false],
                ],
            ],
            [
                'title' => 'Full Stack Symfony Developer',
                'location' => 'Marrakech',
                'description' => 'Work on Symfony + Twig UI features, forms, validation, and integrations with backend services.',
                'closingDate' => $now->modify('+28 days'),
                'skills' => [
                    ['name' => 'Symfony', 'level' => SkillLevel::INTERMEDIATE, 'required' => true],
                    ['name' => 'PHP', 'level' => SkillLevel::ADVANCED, 'required' => true],
                ],
            ],
            [
                'title' => 'Database-focused Backend Engineer',
                'location' => 'Tangier',
                'description' => 'Improve database queries and performance, and support Doctrine mappings and migrations.',
                'closingDate' => $now->modify('+35 days'),
                'skills' => [
                    ['name' => 'SQL', 'level' => SkillLevel::ADVANCED, 'required' => true],
                    ['name' => 'Doctrine ORM', 'level' => SkillLevel::ADVANCED, 'required' => true],
                ],
            ],
        ];

        $createdCount = 0;

        foreach ($seedOffers as $seedOffer) {
            $title = $seedOffer['title'];

            /** @var JobOffer|null $existing */
            $existing = $offerRepository->findOneBy(['title' => $title]);
            if ($existing instanceof JobOffer) {
                continue;
            }

            $offer = new JobOffer();
            $offer->setTitle($title);
            $offer->setDescription($seedOffer['description']);
            $offer->setLocation($seedOffer['location']);
            $offer->setStatus(JobOfferStatus::OPEN);
            $offer->setCreator($creator);
            $offer->setClosingDate(\DateTime::createFromImmutable($seedOffer['closingDate']));

            foreach ($seedOffer['skills'] as $skillRequirement) {
                $skillName = $skillRequirement['name'];
                $skill = $skillsByName[$skillName] ?? null;

                if (!$skill instanceof Skill) {
                    // Defensive: should not happen unless seed skill list is changed.
                    continue;
                }

                $offerSkill = new JobOfferSkill();
                $offerSkill->setSkill($skill);
                $offerSkill->setRequiredLevel($skillRequirement['level']);
                $offerSkill->setIsRequired((bool) $skillRequirement['required']);

                // Owning side is JobOfferSkill.jobOffer; using JobOffer::addSkill keeps relations consistent.
                $offer->addSkill($offerSkill);
            }

            $this->entityManager->persist($offer);
            $createdCount++;
        }

        $output->writeln('<info>Job offers: created ' . $createdCount . ' / 5</info>');
    }
}
