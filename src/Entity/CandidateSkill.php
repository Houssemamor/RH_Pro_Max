<?php

namespace App\Entity;

use App\Enum\SkillLevel;
use App\Repository\CandidateSkillRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CandidateSkillRepository::class)]
class CandidateSkill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(enumType: SkillLevel::class)]
    private ?SkillLevel $level = null;

    #[ORM\Column]
    private ?float $confidence = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CandidateProfile $candidateProfile = null;

    #[ORM\ManyToOne(inversedBy: 'candidateSkills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Skill $skill = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getConfidence(): ?float
    {
        return $this->confidence;
    }

    public function setConfidence(float $confidence): static
    {
        $this->confidence = $confidence;

        return $this;
    }

    public function getCandidateProfile(): ?CandidateProfile
    {
        return $this->candidateProfile;
    }

    public function setCandidateProfile(?CandidateProfile $candidateProfile): static
    {
        $this->candidateProfile = $candidateProfile;

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
}
