<?php

namespace App\Repository;

use App\Entity\JobOfferSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for JobOfferSkill entity
 * 
 * Provides data access methods for job offer skill requirements.
 * 
 * @extends ServiceEntityRepository<JobOfferSkill>
 */
class JobOfferSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JobOfferSkill::class);
    }

    /**
     * Find all required skills for a job offer
     * 
     * @param int $jobOfferId
     * @return JobOfferSkill[]
     */
    public function findRequiredSkillsByJobOffer(int $jobOfferId): array
    {
        return $this->createQueryBuilder('jos')
            ->andWhere('jos.jobOffer = :jobOfferId')
            ->andWhere('jos.isRequired = :required')
            ->setParameter('jobOfferId', $jobOfferId)
            ->setParameter('required', true)
            ->orderBy('jos.requiredLevel', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all optional skills for a job offer
     * 
     * @param int $jobOfferId
     * @return JobOfferSkill[]
     */
    public function findOptionalSkillsByJobOffer(int $jobOfferId): array
    {
        return $this->createQueryBuilder('jos')
            ->andWhere('jos.jobOffer = :jobOfferId')
            ->andWhere('jos.isRequired = :required')
            ->setParameter('jobOfferId', $jobOfferId)
            ->setParameter('required', false)
            ->orderBy('jos.requiredLevel', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
