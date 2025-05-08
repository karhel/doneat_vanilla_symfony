<?php

namespace App\Controller;

use App\Entity\Meal;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\BookMealForm;
use App\Form\CreateMealForm;
use App\Form\DeleteMealForm;
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
    #[Route('/meal/geoloc', name: 'app_meal_geo_test')]
    public function geoloc(MealRepository $mealRepository, Request $request): Response
    {        
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $meals = $mealRepository->findByDistanceFrom(
            $currentUser->getMainAddress()->getLatitude(), 
            $currentUser->getMainAddress()->getLongitude(), 
            10); // Distance de 10 kms

        return $this->render('meal/geoloc.html.twig', [
            'user' => $currentUser,
            'meals' => $meals
        ]);
    }

    #[Route('/meal', name: 'app_meal')]
    public function index(): Response
    {
        return $this->render('meal/index.html.twig');
    }

    #[Route('/meal/post', name: 'app_meal_create')]
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, FileUploader $fileUploader): Response
    {             
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $meal = new Meal();
        $meal->setAddress($currentUser->getAddress());
        
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

            $meal
                ->setCreatedAt(new DateTimeImmutable())
                ->setCreatedBy($currentUser);

            if(!($meal->getLatitude() && $meal->getLongitude())) {
                $meal
                    ->setLatitude($currentUser->getMainAddress()->getLatitude())
                    ->setLongitude($currentUser->getMainAddress()->getLongitude());
            }

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

        $editForm = $this->createForm(CreateMealForm::class, $meal);
        
        $deleteForm = $this->createForm(DeleteMealForm::class, $meal, [
            'action'    => $this->generateUrl('app_meal_delete', ['id' => $meal->getId()])
        ]);

        $editForm->handleRequest($request);
        
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            try {

                $picture = $editForm->get('imageFile')->getData();

                if($picture) {

                    $currentPictureFilename = $meal->getPicture();

                    
                        // Si une image existe déjà, je la supprime
                        if($currentPictureFilename) {
                            $fileUploader->remove($currentPictureFilename);

                            $newFilename = $fileUploader->upload($picture); //< Utilisation du service
                            $meal->setPicture($newFilename);
                        }

                }  
                

                $entityManager->flush();

                return $this->redirectToRoute('app_meal');    
                
            }
            catch(FileException $e) {

                $logger->error("UPDATE MEAL ERROR" . $e->getMessage());
            }      
        }

        return $this->render('meal/edit.html.twig', [
            'mealEditForm'      => $editForm,
            'mealDeleteForm'    => $deleteForm
        ]);
    }


    #[Route('/meal/book/{id<\d+>}', name: 'app_meal_book', methods: ['GET', 'POST'])]
    #[IsGranted('book', subject: 'meal', message: "Vous ne pouvez pas réserver ce repas")]
    public function book(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $form = $this->createForm(BookMealForm::class, $meal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $meal
                ->setBookedAt(new DateTimeImmutable())
                ->setBookedBy($this->getUser());

            $entityManager->flush();

            return $this->redirectToRoute('app_meal'); 
        }

        return $this->render('meal/book.html.twig', [
            'mealBookForm' => $form,
            'meal' => $meal
        ]);
    }

    #[Route('/meal/delete/{id<\d+>}', name: 'app_meal_delete', methods: ['POST'])]
    public function delete(Meal $meal, FileUploader $fileUploader, EntityManagerInterface $entityManager): Response
    {
        $picture = $meal->getPicture();
        
        // Si une image existe déjà, je la supprime
        if($picture) {
            $fileUploader->remove($picture);
        }

        $entityManager->remove($meal);
        $entityManager->flush();

        return $this->redirectToRoute('app_meal'); 
    }

    #[Route('/meal/picture/stream/{id<\d+>}', name: 'app_meal_picture_stream', methods: ['GET'])]
    public function getStreamPicture(Meal $meal, FileUploader $fileUploader): Response
    {
        return $fileUploader->readStream($meal->getPicture());
    }
}
