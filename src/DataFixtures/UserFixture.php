<?php

namespace App\DataFixtures;

use App\Entity\Address;
use Faker\Factory;
use App\Entity\User;
use Faker\Generator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    const NUMBER_OF_USERS = 5;

    private Generator $faker;

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) 
    {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        $objUser = new User();
        
        $plainPassword = "Azerty123!&";

        $objUser
            ->setEmail('jdoe@app.net')
            ->setFirstname('John')
            ->setLastname('Doe')
            ->setPassword($this->userPasswordHasher->hashPassword($objUser, $plainPassword))
            ->setRoles(["ROLE_ADMIN"]);

        $objAddress = new Address();
        $objAddress->setAddress($this->faker->address());

        $manager->persist($objAddress);

        $objUser->setMainAddress($objAddress);

        $manager->persist($objUser);
        
        for($i = 0; $i < self::NUMBER_OF_USERS; $i++) {

            $objUser = new User();
            $objUser->setEmail($this->faker->email())
                ->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setPassword($this->userPasswordHasher->hashPassword($objUser, $this->faker->password()));

            $objAddress = new Address();
            $objAddress->setAddress($this->faker->address());

            $manager->persist($objAddress);

            $objUser->setMainAddress($objAddress);

            $manager->persist($objUser);
        }

        $manager->flush();
    }
}
