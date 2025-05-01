<?php

namespace App\Controller;

use App\Entity\Meal;
use DateTimeImmutable;
use App\Form\CreateMealForm;
use Psr\Log\LoggerInterface;
use App\Service\FileUploader;
use App\Repository\MealRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, FileUploader $fileUploader): Response
    {             
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $meal = new Meal();
        $form = $this->createForm(CreateMealForm::class, $meal);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $picture = $form->get('imageFile')->getData();
            if($picture) {

                try {
                    $newFilename = $fileUploader->upload($picture); //< Utilisation du service
                    $meal->setPicture($newFilename);
                }
                catch(FileException $e) {
                    $logger->error($e->getMessage());
                }
            }

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

    #[Route('meal/edit/{id<\d+>}', name: 'app_meal_edit', methods: ['GET', 'POST'])]
    #[IsGranted('edit', subject: 'meal', message: "Vous n'avez pas les droits pour modifier ce repas")]
    public function edit(Meal $meal, FileUploader $fileUploader, Request $request, 
        EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {  
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $form = $this->createForm(CreateMealForm::class, $meal);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {

            $picture = $form->get('imageFile')->getData();

            if($picture) {

                $currentPictureFilename = $meal->getPicture();

                try {
                    // Si une image existe déjà, je la supprime
                    if($currentPictureFilename) {
                        $fileUploader->remove($currentPictureFilename);
                    }

                    $newFilename = $fileUploader->upload($picture); //< Utilisation du service
                    $meal->setPicture($newFilename);

                    $entityManager->flush();

                    return $this->redirectToRoute('app_meal');    
                    
                }
                catch(FileException $e) {

                    $logger->error($e->getMessage());
                }
            }        
        }

        return $this->render('meal/edit.html.twig', [
            'mealEditForm' => $form
        ]);

    }

    #[Route('/meal/picture/stream/{id<\d+>}', name: 'app_meal_picture_stream', methods: ['GET'])]
    public function getStreamPicture(Meal $meal, FileUploader $fileUploader): Response
    {
        return $fileUploader->readStream($meal->getPicture());
    }
}
