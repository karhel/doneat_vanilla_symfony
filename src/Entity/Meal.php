<?php

namespace App\Entity;

use App\Repository\MealRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Bazinga\GeocoderBundle\Mapping\Annotations as Geocoder;

#[ORM\Entity(repositoryClass: MealRepository::class)]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    /**
     * @var Collection<int, MealTag>
     */
    #[ORM\ManyToMany(targetEntity: MealTag::class, inversedBy: 'meals')]
    private Collection $tags;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'createdMeals')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $createdBy = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Address $location = null;

    /**
     * @var Collection<int, BookingRequest>
     */
    #[ORM\OneToMany(targetEntity: BookingRequest::class, mappedBy: 'meal')]
    private Collection $bookingRequests;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->bookingRequests = new ArrayCollection();
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

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, MealTag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(MealTag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(MealTag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getLocation(): ?Address
    {
        return $this->location;
    }

    public function setLocation(?Address $location): static
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return Collection<int, BookingRequest>
     */
    public function getBookingRequests(): Collection
    {
        return $this->bookingRequests;
    }

    public function addBookingRequest(BookingRequest $bookingRequest): static
    {
        if (!$this->bookingRequests->contains($bookingRequest)) {
            $this->bookingRequests->add($bookingRequest);
            $bookingRequest->setMeal($this);
        }

        return $this;
    }

    public function removeBookingRequest(BookingRequest $bookingRequest): static
    {
        if ($this->bookingRequests->removeElement($bookingRequest)) {
            // set the owning side to null (unless already changed)
            if ($bookingRequest->getMeal() === $this) {
                $bookingRequest->setMeal(null);
            }
        }

        return $this;
    }
}
