<?php

namespace App\Entity;

use App\Repository\BookingRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BookingRequestRepository::class)]
class BookingRequest
{
    public const STATUS_PENDING     = 0;
    public const STATUS_VALIDATED   = 1;
    public const STATUS_REFUSED     = 2;
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookingRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $requestedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $requestedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $validatedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $requestComment = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $validationComment = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedAt = null;

    #[ORM\ManyToOne(inversedBy: 'bookingRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Meal $meal = null;

    #[ORM\Column]
    private ?int $status = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedByGiverAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedByEaterAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRequestedBy(): ?User
    {
        return $this->requestedBy;
    }

    public function setRequestedBy(?User $requestedBy): static
    {
        $this->requestedBy = $requestedBy;

        return $this;
    }

    public function getRequestedAt(): ?\DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function setRequestedAt(\DateTimeImmutable $requestedAt): static
    {
        $this->requestedAt = $requestedAt;

        return $this;
    }

    public function getValidatedAt(): ?\DateTimeImmutable
    {
        return $this->validatedAt;
    }

    public function setValidatedAt(\DateTimeImmutable $validatedAt): static
    {
        $this->validatedAt = $validatedAt;

        return $this;
    }

    public function getRequestComment(): ?string
    {
        return $this->requestComment;
    }

    public function setRequestComment(?string $requestComment): static
    {
        $this->requestComment = $requestComment;

        return $this;
    }

    public function getValidationComment(): ?string
    {
        return $this->validationComment;
    }

    public function setValidationComment(string $validationComment): static
    {
        $this->validationComment = $validationComment;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeImmutable $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getMeal(): ?Meal
    {
        return $this->meal;
    }

    public function setMeal(?Meal $meal): static
    {
        $this->meal = $meal;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getClosedByGiverAt(): ?\DateTimeImmutable
    {
        return $this->closedByGiverAt;
    }

    public function setClosedByGiverAt(?\DateTimeImmutable $closedByGiverAt): static
    {
        $this->closedByGiverAt = $closedByGiverAt;

        return $this;
    }

    public function getClosedByEaterAt(): ?\DateTimeImmutable
    {
        return $this->closedByEaterAt;
    }

    public function setClosedByEaterAt(?\DateTimeImmutable $closedByEaterAt): static
    {
        $this->closedByEaterAt = $closedByEaterAt;

        return $this;
    }
}
