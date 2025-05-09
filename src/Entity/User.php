<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Bazinga\GeocoderBundle\Mapping\Annotations as Geocoder;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
#[Geocoder\Geocodeable()]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column]
    private bool $isVerified = false;

    #[ORM\Column(length: 150)]
    private ?string $firstname = null;

    #[ORM\Column(length: 150)]
    private ?string $lastname = null;

    /**
     * @var Collection<int, Meal>
     */
    #[ORM\OneToMany(targetEntity: Meal::class, mappedBy: 'createdBy', orphanRemoval: true)]
    private Collection $createdMeals;

    /**
     * @var Collection<int, Meal>
     */
    #[ORM\OneToMany(targetEntity: Meal::class, mappedBy: 'bookedBy')]
    private Collection $bookedMeals;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Address $mainAddress = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Geocoder\Address()]
    private ?string $address = null;
    
    #[ORM\Column(nullable: true)]
    #[Geocoder\Latitude()]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Geocoder\Longitude()]
    private ?float $longitude = null;

    /**
     * @var Collection<int, MealBookRequest>
     */
    #[ORM\OneToMany(targetEntity: MealBookRequest::class, mappedBy: 'requestedBy')]
    private Collection $mealBookRequests;

    public function __construct()
    {
        $this->createdMeals = new ArrayCollection();
        $this->bookedMeals = new ArrayCollection();
        $this->mealBookRequests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return Collection<int, Meal>
     */
    public function getCreatedMeals(): Collection
    {
        return $this->createdMeals;
    }

    public function addCreatedMeal(Meal $createdMeal): static
    {
        if (!$this->createdMeals->contains($createdMeal)) {
            $this->createdMeals->add($createdMeal);
            $createdMeal->setCreatedBy($this);
        }

        return $this;
    }

    public function removeCreatedMeal(Meal $createdMeal): static
    {
        if ($this->createdMeals->removeElement($createdMeal)) {
            // set the owning side to null (unless already changed)
            if ($createdMeal->getCreatedBy() === $this) {
                $createdMeal->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Meal>
     */
    public function getBookedMeals(): Collection
    {
        return $this->bookedMeals;
    }

    public function addBookedMeal(Meal $bookedMeal): static
    {
        if (!$this->bookedMeals->contains($bookedMeal)) {
            $this->bookedMeals->add($bookedMeal);
            $bookedMeal->setBookedBy($this);
        }

        return $this;
    }

    public function removeBookedMeal(Meal $bookedMeal): static
    {
        if ($this->bookedMeals->removeElement($bookedMeal)) {
            // set the owning side to null (unless already changed)
            if ($bookedMeal->getBookedBy() === $this) {
                $bookedMeal->setBookedBy(null);
            }
        }

        return $this;
    }

    public function getMainAddress(): ?Address
    {
        return $this->mainAddress;
    }

    public function setMainAddress(?Address $mainAddress): static
    {
        $this->mainAddress = $mainAddress;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(?float $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(?float $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return Collection<int, MealBookRequest>
     */
    public function getMealBookRequests(): Collection
    {
        return $this->mealBookRequests;
    }

    public function addMealBookRequest(MealBookRequest $mealBookRequest): static
    {
        if (!$this->mealBookRequests->contains($mealBookRequest)) {
            $this->mealBookRequests->add($mealBookRequest);
            $mealBookRequest->setRequestedBy($this);
        }

        return $this;
    }

    public function removeMealBookRequest(MealBookRequest $mealBookRequest): static
    {
        if ($this->mealBookRequests->removeElement($mealBookRequest)) {
            // set the owning side to null (unless already changed)
            if ($mealBookRequest->getRequestedBy() === $this) {
                $mealBookRequest->setRequestedBy(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->firstname . " " . $this->lastname;
    }
}
