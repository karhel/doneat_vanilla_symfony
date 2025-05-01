<?php

namespace App\Twig\Components;

use App\Entity\Meal;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class MealCard
{
    public Meal $meal;
    public bool $actions = true;
}
