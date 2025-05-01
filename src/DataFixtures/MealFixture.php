<?php

namespace App\DataFixtures;

use App\Entity\Meal;
use App\Entity\User;
use DateTimeImmutable;
use App\Entity\MealTag;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Faker\Generator;

class MealFixture extends Fixture implements DependentFixtureInterface
{
    const NUMBER_OF_MEAL_PER_USER = [1, 5];

    private Generator $faker;

    public function __construct() 
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $mealTags = [
            ["Vegan", false],
            ["Lactose", true],
            ["Arachides", true],
            ["Gluten", true]
        ];

        foreach($mealTags as $arrTag) {
            $objMealTag = new MealTag();
            $objMealTag
                ->setName($arrTag[0])
                ->setIsAllergen($arrTag[1]);
            
            $manager->persist($objMealTag);
        }

        $arrUsers = $manager->getRepository(User::class)->findAll();
        foreach($arrUsers as $user)
        {
            $nbrOfMeals = $this->faker->numberBetween(self::NUMBER_OF_MEAL_PER_USER[0], self::NUMBER_OF_MEAL_PER_USER[1]);

            for($i = 0; $i < $nbrOfMeals; $i++) {

                $objMeal = new Meal();
                $objMeal
                    ->setTitle($this->faker->sentence(5))
                    ->setDescription($this->faker->text())
                    ->setCreatedBy($user)
                    ->setCreatedAt(new DateTimeImmutable());

                $manager->persist($objMeal);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class
        ];
    }
}
