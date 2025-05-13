<?php

namespace App\Entity;

use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Bazinga\GeocoderBundle\Mapping\Annotations as Geocoder;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
#[Geocoder\Geocodeable()]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    
    #[ORM\Column(length: 255, nullable: true)]
    #[Geocoder\Address()]
    private ?string $address = null;
    
    #[ORM\Column(nullable: true)]
    #[Geocoder\Latitude()]
    private ?float $latitude = null;

    #[ORM\Column(nullable: true)]
    #[Geocoder\Longitude()]
    private ?float $longitude = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

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
}
