<?php

namespace App\Entity;

use App\Repository\SkillRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SkillRepository::class)]
class Skill
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'skills')]
    #[ORM\JoinColumn(nullable: false)]
    private ?SkillCategory $category = null;

    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: EmployeeSkill::class)]
    private Collection $employeeSkills;

    #[ORM\OneToMany(mappedBy: 'skill', targetEntity: CandidateSkill::class)]
    private Collection $candidateSkills;

    public function __construct()
    {
        $this->employeeSkills = new ArrayCollection();
        $this->candidateSkills = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?SkillCategory
    {
        return $this->category;
    }

    public function setCategory(?SkillCategory $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, EmployeeSkill>
     */
    public function getEmployeeSkills(): Collection
    {
        return $this->employeeSkills;
    }

    public function addEmployeeSkill(EmployeeSkill $employeeSkill): static
    {
        if (!$this->employeeSkills->contains($employeeSkill)) {
            $this->employeeSkills->add($employeeSkill);
            $employeeSkill->setSkill($this);
        }

        return $this;
    }

    public function removeEmployeeSkill(EmployeeSkill $employeeSkill): static
    {
        if ($this->employeeSkills->removeElement($employeeSkill)) {
            // set the owning side to null (unless already changed)
            if ($employeeSkill->getSkill() === $this) {
                $employeeSkill->setSkill(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CandidateSkill>
     */
    public function getCandidateSkills(): Collection
    {
        return $this->candidateSkills;
    }

    public function addCandidateSkill(CandidateSkill $candidateSkill): static
    {
        if (!$this->candidateSkills->contains($candidateSkill)) {
            $this->candidateSkills->add($candidateSkill);
            $candidateSkill->setSkill($this);
        }

        return $this;
    }

    public function removeCandidateSkill(CandidateSkill $candidateSkill): static
    {
        if ($this->candidateSkills->removeElement($candidateSkill)) {
            // set the owning side to null (unless already changed)
            if ($candidateSkill->getSkill() === $this) {
                $candidateSkill->setSkill(null);
            }
        }

        return $this;
    }
}
