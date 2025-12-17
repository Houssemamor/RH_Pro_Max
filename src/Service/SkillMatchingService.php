<?php

namespace App\Service;

use App\Entity\CandidateProfile;
use App\Entity\CandidateSkill;
use App\Entity\JobOffer;
use App\Entity\JobOfferSkill;
use App\Enum\SkillLevel;

/**
 * SkillMatchingService
 * 
 * Purpose: Calculates skill compatibility between candidates and job offers.
 * Compares candidate skills against job requirements to produce a match score
 * and detailed skill breakdown.
 * 
 * Design Pattern: Service/Strategy Pattern
 * SOLID Principles:
 * - Single Responsibility: Only handles skill matching logic
 * - Open/Closed: Extensible for different matching algorithms
 * - Dependency Inversion: Depends on abstractions (entities) not concrete implementations
 */
class SkillMatchingService
{
    /**
     * Skill level hierarchy for comparison
     * Higher values = higher proficiency
     */
    private const SKILL_LEVEL_WEIGHTS = [
        SkillLevel::BEGINNER->value => 1,
        SkillLevel::INTERMEDIATE->value => 2,
        SkillLevel::ADVANCED->value => 3,
        SkillLevel::EXPERT->value => 4,
    ];

    /**
     * Calculate skill match between a candidate and a job offer
     * 
     * Returns detailed match information:
     * - matchPercentage: 0-100, overall compatibility score
     * - matchedSkills: Skills the candidate has that meet/exceed requirements
     * - missingSkills: Required skills the candidate lacks
     * - bonusSkills: Optional skills the candidate has
     * - exceedingSkills: Skills where candidate exceeds requirements
     * 
     * @param CandidateProfile $candidate
     * @param JobOffer $jobOffer
     * @return array{
     *     matchPercentage: float,
     *     matchedSkills: array<JobOfferSkill>,
     *     missingSkills: array<JobOfferSkill>,
     *     bonusSkills: array<JobOfferSkill>,
     *     exceedingSkills: array<JobOfferSkill>,
     *     totalRequired: int,
     *     totalOptional: int
     * }
     */
    public function calculateMatch(CandidateProfile $candidate, JobOffer $jobOffer): array
    {
        $candidateSkills = $this->indexCandidateSkills($candidate);
        $jobSkills = $jobOffer->getSkills();

        $matchedSkills = [];
        $missingSkills = [];
        $bonusSkills = [];
        $exceedingSkills = [];

        $totalRequired = 0;
        $matchedRequired = 0;
        $totalOptional = 0;
        $matchedOptional = 0;

        foreach ($jobSkills as $jobSkill) {
            $skillId = $jobSkill->getSkill()->getId();
            $candidateHasSkill = isset($candidateSkills[$skillId]);

            if ($jobSkill->isRequired()) {
                $totalRequired++;
                
                if ($candidateHasSkill) {
                    $candidateLevel = $candidateSkills[$skillId]->getLevel();
                    
                    if ($this->meetsRequirement($candidateLevel, $jobSkill->getRequiredLevel())) {
                        $matchedRequired++;
                        $matchedSkills[] = $jobSkill;
                        
                        // Check if candidate exceeds requirement
                        if ($this->exceeds($candidateLevel, $jobSkill->getRequiredLevel())) {
                            $exceedingSkills[] = $jobSkill;
                        }
                    } else {
                        // Has skill but insufficient level
                        $missingSkills[] = $jobSkill;
                    }
                } else {
                    // Candidate lacks required skill
                    $missingSkills[] = $jobSkill;
                }
            } else {
                // Optional skill
                $totalOptional++;
                
                if ($candidateHasSkill) {
                    $candidateLevel = $candidateSkills[$skillId]->getLevel();
                    
                    if ($this->meetsRequirement($candidateLevel, $jobSkill->getRequiredLevel())) {
                        $matchedOptional++;
                        $bonusSkills[] = $jobSkill;
                        
                        if ($this->exceeds($candidateLevel, $jobSkill->getRequiredLevel())) {
                            $exceedingSkills[] = $jobSkill;
                        }
                    }
                }
            }
        }

        // Calculate match percentage
        // Required skills weigh heavily (70%), optional skills add bonus (30%)
        $matchPercentage = $this->calculatePercentage(
            $matchedRequired,
            $totalRequired,
            $matchedOptional,
            $totalOptional
        );

        return [
            'matchPercentage' => $matchPercentage,
            'matchedSkills' => $matchedSkills,
            'missingSkills' => $missingSkills,
            'bonusSkills' => $bonusSkills,
            'exceedingSkills' => $exceedingSkills,
            'totalRequired' => $totalRequired,
            'totalOptional' => $totalOptional,
        ];
    }

    /**
     * Index candidate skills by skill ID for fast lookup
     * 
     * @param CandidateProfile $candidate
     * @return array<int, CandidateSkill>
     */
    private function indexCandidateSkills(CandidateProfile $candidate): array
    {
        $indexed = [];
        foreach ($candidate->getSkills() as $candidateSkill) {
            $skillId = $candidateSkill->getSkill()->getId();
            $indexed[$skillId] = $candidateSkill;
        }
        return $indexed;
    }

    /**
     * Check if candidate's skill level meets job requirement
     * Candidates with higher levels automatically meet lower requirements
     * 
     * @param SkillLevel $candidateLevel
     * @param SkillLevel $requiredLevel
     * @return bool
     */
    private function meetsRequirement(SkillLevel $candidateLevel, SkillLevel $requiredLevel): bool
    {
        return self::SKILL_LEVEL_WEIGHTS[$candidateLevel->value] 
            >= self::SKILL_LEVEL_WEIGHTS[$requiredLevel->value];
    }

    /**
     * Check if candidate's skill level exceeds job requirement
     * 
     * @param SkillLevel $candidateLevel
     * @param SkillLevel $requiredLevel
     * @return bool
     */
    private function exceeds(SkillLevel $candidateLevel, SkillLevel $requiredLevel): bool
    {
        return self::SKILL_LEVEL_WEIGHTS[$candidateLevel->value] 
            > self::SKILL_LEVEL_WEIGHTS[$requiredLevel->value];
    }

    /**
     * Calculate overall match percentage
     * 
     * Weighted formula:
     * - Required skills: 70% weight
     * - Optional skills: 30% weight (bonus)
     * 
     * If no required skills, base score on optional skills only.
     * If no skills at all, return 0%.
     * 
     * @param int $matchedRequired
     * @param int $totalRequired
     * @param int $matchedOptional
     * @param int $totalOptional
     * @return float
     */
    private function calculatePercentage(
        int $matchedRequired,
        int $totalRequired,
        int $matchedOptional,
        int $totalOptional
    ): float {
        // No skills defined in job offer
        if ($totalRequired === 0 && $totalOptional === 0) {
            return 100.0; // No requirements = perfect match
        }

        $requiredScore = 0.0;
        $optionalScore = 0.0;

        // Calculate required skills score (70% weight)
        if ($totalRequired > 0) {
            $requiredScore = ($matchedRequired / $totalRequired) * 70;
        } else {
            // No required skills, so give full 70% base score
            $requiredScore = 70.0;
        }

        // Calculate optional skills score (30% weight)
        if ($totalOptional > 0) {
            $optionalScore = ($matchedOptional / $totalOptional) * 30;
        }

        return round($requiredScore + $optionalScore, 2);
    }

    /**
     * Get match level classification based on percentage
     * 
     * @param float $matchPercentage
     * @return string 'excellent'|'good'|'fair'|'poor'
     */
    public function getMatchLevel(float $matchPercentage): string
    {
        if ($matchPercentage >= 80) {
            return 'excellent';
        } elseif ($matchPercentage >= 60) {
            return 'good';
        } elseif ($matchPercentage >= 40) {
            return 'fair';
        } else {
            return 'poor';
        }
    }
}
