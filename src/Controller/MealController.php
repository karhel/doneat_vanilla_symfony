<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Form\CreateMealForm;
use App\Repository\MealRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MealController extends AbstractController
{
    #[Route('/meal', name: 'app_meal')]
    public function index(MealRepository $mealRepository): Response
    {
        $meals = $mealRepository->findAll();

        return $this->render('meal/index.html.twig', [
            'meals' => $meals,
        ]);
    }

    #[Route('/meal/post', name: 'app_meal_create')]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {             
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $meal = new Meal();
        $form = $this->createForm(CreateMealForm::class, $meal);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $meal->setCreatedAt(new DateTimeImmutable());
            $meal->setCreatedBy($this->getUser());

            $entityManager->persist($meal);
            $entityManager->flush();

            return $this->redirectToRoute('app_meal');            
        }

        return $this->render('meal/create.html.twig', [
            'mealCreateForm' => $form
        ]);
    }
}
