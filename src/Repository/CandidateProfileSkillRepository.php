<?php

namespace App\Repository;

use App\Entity\CandidateProfileSkill;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository for CandidateProfileSkill entity
 * 
 * @extends ServiceEntityRepository<CandidateProfileSkill>
 */
class CandidateProfileSkillRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CandidateProfileSkill::class);
    }

    /**
     * Find all skills for a candidate
     * 
     * @param int $candidateId
     * @return CandidateProfileSkill[]
     */
    public function findByCandidate(int $candidateId): array
    {
        return $this->createQueryBuilder('cps')
            ->andWhere('cps.candidate = :candidateId')
            ->setParameter('candidateId', $candidateId)
            ->orderBy('cps.level', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
