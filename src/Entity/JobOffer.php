<?php

namespace App\Entity;

use App\Enum\JobOfferStatus;
use App\Repository\JobOfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobOfferRepository::class)]
class JobOffer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $location = null;

    #[ORM\Column(enumType: JobOfferStatus::class)]
    private ?JobOfferStatus $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $closingDate = null;

    #[ORM\ManyToOne(inversedBy: 'createdOffers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $creator = null;

    #[ORM\OneToMany(mappedBy: 'jobOffer', targetEntity: CandidateProfile::class)]
    private Collection $candidates;

    /**
     * Skills required or recommended for this job offer
     */
    #[ORM\OneToMany(mappedBy: 'jobOffer', targetEntity: JobOfferSkill::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $skills;

    public function __construct()
    {
        $this->candidates = new ArrayCollection();
        $this->skills = new ArrayCollection();
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getStatus(): ?JobOfferStatus
    {
        return $this->status;
    }

    public function setStatus(JobOfferStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(\DateTimeInterface $closingDate): static
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, CandidateProfile>
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    public function addCandidate(CandidateProfile $candidate): static
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates->add($candidate);
            $candidate->setJobOffer($this);
        }

        return $this;
    }

    public function removeCandidate(CandidateProfile $candidate): static
    {
        if ($this->candidates->removeElement($candidate)) {
            // set the owning side to null (unless already changed)
            if ($candidate->getJobOffer() === $this) {
                $candidate->setJobOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, JobOfferSkill>
     */
    public function getSkills(): Collection
    {
        return $this->skills;
    }

    public function addSkill(JobOfferSkill $skill): static
    {
        if (!$this->skills->contains($skill)) {
            $this->skills->add($skill);
            $skill->setJobOffer($this);
        }

        return $this;
    }

    public function removeSkill(JobOfferSkill $skill): static
    {
        if ($this->skills->removeElement($skill)) {
            // set the owning side to null (unless already changed)
            if ($skill->getJobOffer() === $this) {
                $skill->setJobOffer(null);
            }
        }

        return $this;
    }

    /**
     * Get all required skills (mandatory for the position)
     * 
     * @return array<JobOfferSkill>
     */
    public function getRequiredSkills(): array
    {
        return $this->skills->filter(fn(JobOfferSkill $skill) => $skill->isRequired())->toArray();
    }

    /**
     * Get all optional skills (nice to have)
     * 
     * @return array<JobOfferSkill>
     */
    public function getOptionalSkills(): array
    {
        return $this->skills->filter(fn(JobOfferSkill $skill) => !$skill->isRequired())->toArray();
    }
}
