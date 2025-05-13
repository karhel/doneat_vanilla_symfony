<?php

namespace App\Twig\Components;

use App\Repository\MealRepository;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

#[AsLiveComponent('MealInfinitGrid')]
final class MealInfinitGrid
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    private const PER_PAGE = 8;

    #[LiveProp]
    public int $page = 1;

    public int $distance = 0;
    
    public float $latitude = .0;
    public float $longitude = .0;

    public function __construct(
        private MealRepository $mealRepository
    )
    {

    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function hasMore(): bool
    {
        if($this->distance >= 0) {

            return $this->mealRepository->countAvailable($this->distance, $this->latitude, $this->longitude) > ($this->page * self::PER_PAGE);
        }

        return $this->mealRepository->countAvailable() > ($this->page * self::PER_PAGE);
    }

    public function getItems(): array
    {
        if($this->distance >= 0) {
            
            return $this->mealRepository->paginateAvailable($this->page, self::PER_PAGE, $this->distance, $this->latitude, $this->longitude);
        }

        return $this->mealRepository->paginateAvailable($this->page, self::PER_PAGE);
    }
}
