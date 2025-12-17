<?php

namespace App\Entity;

use App\Enum\SkillLevel;
use App\Repository\CandidateProfileSkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * CandidateProfileSkill Entity
 * 
 * Purpose: Stores skills in the candidate's general profile (not tied to a specific job application).
 * These skills are copied to CandidateProfile (application) when the candidate applies for a job.
 * 
 * Design Pattern: Entity/Value Object
 * SOLID Principle: Single Responsibility - manages candidate's personal skill inventory
 */
#[ORM\Entity(repositoryClass: CandidateProfileSkillRepository::class)]
class CandidateProfileSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The candidate who owns this skill
     */
    #[ORM\ManyToOne(inversedBy: 'profileSkills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Candidate $candidate = null;

    /**
     * The skill
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    /**
     * Proficiency level for this skill
     */
    #[ORM\Column(enumType: SkillLevel::class)]
    private ?SkillLevel $level = null;

    /**
     * Years of experience with this skill
     */
    #[ORM\Column(nullable: true)]
    private ?int $yearsExperience = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCandidate(): ?Candidate
    {
        return $this->candidate;
    }

    public function setCandidate(?Candidate $candidate): static
    {
        $this->candidate = $candidate;

        return $this;
    }

    public function getSkill(): ?Skill
    {
        return $this->skill;
    }

    public function setSkill(?Skill $skill): static
    {
        $this->skill = $skill;

        return $this;
    }

    public function getLevel(): ?SkillLevel
    {
        return $this->level;
    }

    public function setLevel(SkillLevel $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getYearsExperience(): ?int
    {
        return $this->yearsExperience;
    }

    public function setYearsExperience(?int $yearsExperience): static
    {
        $this->yearsExperience = $yearsExperience;

        return $this;
    }
}
