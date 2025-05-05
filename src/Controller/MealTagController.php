<?php

namespace App\Controller;

use App\Entity\MealTag;
use App\Form\CreateMealTagForm;
use App\Repository\MealTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class MealTagController extends AbstractController
{
    #[Route('/settings/mealtag', name: 'app_mealtag', methods: ['GET'])]
    public function index(MealTagRepository $mealTagRepository): Response
    {
        return $this->render('mealtag/index.html.twig', [
            'mealTags' => $mealTagRepository->findAll()
        ]);
    }

    #[Route('/settings/mealtag/create', name: 'app_mealtag_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $mealTag = new MealTag();

        $form = $this->createForm(CreateMealTagForm::class, $mealTag);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($mealTag);
            $entityManager->flush();

            return $this->redirectToRoute('app_mealtag');   
        }

        return $this->render('mealtag/create.html.twig', [
            'mealTagCreateForm' => $form
        ]);
    }
}
