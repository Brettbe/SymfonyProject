<?php

namespace App\Entity;

use App\Repository\OfferRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OfferRepository::class)]
class Offer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['offer:read'])]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Gedmo\Timestampable(on:'create')]
    #[Groups(['offer:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    #[Gedmo\Timestampable(on:'update')]
    #[Groups(['offer:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\Column(length: 255)]
    #[Groups(['offer:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['offer:read'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['offer:read'])]
    private ?string $content = null;

    #[ORM\ManyToOne(inversedBy: 'offers')]
    private ?User $recruiter = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'candidate')]
    private Collection $users;

    #[ORM\Column(length: 255)]
    private ?string $tag = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255)]
    #[Groups(['offer:read'])]
    private ?string $status = null;

    public function __construct()
    {
        $this->users = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getRecruiter(): ?User
    {
        return $this->recruiter;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function setRecruiter(?User $recruiter): static
    {
        $this->recruiter = $recruiter;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addCandidate($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeCandidate($this);
        }

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }
}
