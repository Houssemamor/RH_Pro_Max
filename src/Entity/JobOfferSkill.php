<?php

namespace App\Entity;

use App\Enum\SkillLevel;
use App\Repository\JobOfferSkillRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * JobOfferSkill Entity
 * 
 * Purpose: Links skills to job offers with required proficiency levels.
 * Allows HR managers to specify which skills are needed for a position
 * and whether they are mandatory or optional.
 * 
 * Design Pattern: Entity/Value Object
 * SOLID Principle: Single Responsibility - manages only job offer skill requirements
 */
#[ORM\Entity(repositoryClass: JobOfferSkillRepository::class)]
class JobOfferSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * The job offer that requires this skill
     */
    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?JobOffer $jobOffer = null;

    /**
     * The skill that is required
     */
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    /**
     * Minimum proficiency level required for this skill
     * BEGINNER < INTERMEDIATE < ADVANCED < EXPERT
     */
    #[ORM\Column(enumType: SkillLevel::class)]
    private ?SkillLevel $requiredLevel = null;

    /**
     * Indicates whether this skill is mandatory for the position
     * true = required (candidate must have)
     * false = optional (nice to have, bonus)
     */
    #[ORM\Column]
    private ?bool $isRequired = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobOffer(): ?JobOffer
    {
        return $this->jobOffer;
    }

    public function setJobOffer(?JobOffer $jobOffer): static
    {
        $this->jobOffer = $jobOffer;

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

    public function getRequiredLevel(): ?SkillLevel
    {
        return $this->requiredLevel;
    }

    public function setRequiredLevel(SkillLevel $requiredLevel): static
    {
        $this->requiredLevel = $requiredLevel;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;

        return $this;
    }
}
