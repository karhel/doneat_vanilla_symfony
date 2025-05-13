<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Meal;
use App\Entity\User;
use DateTimeImmutable;
use App\Form\BookMealForm;
use App\Form\CreateMealForm;
use App\Form\DeleteMealForm;
use App\Form\MealCreateForm;
use App\Form\MealDeleteForm;
use App\Form\MealUpdateForm;
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
    #[Route('/meal', name: 'app_meal', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('meal/index.html.twig');
    }

    #[Route('/meal/create', name: 'app_meal_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, FileUploader $fileUploader): Response
    { 
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $meal = new Meal();

        $createForm = $this->createForm(MealCreateForm::class, $meal);

        $createForm->handleRequest($request);

        if($createForm->isSubmitted() && $createForm->isValid())
        {
            $picture = $createForm->get('imageFile')->getData();
            if($picture) {

                try {

                    $newFilename = $fileUploader->upload($picture); //< Utilisation du service
                    $meal->setPicture($newFilename);
                }
                catch(FileException $e) {

                    $logger->error($e->getMessage());
                }
            }

            $location = $currentUser->getMainAddress();
            
            if(!$location) {

                $this->addFlash('error', "Nous n'avons pas pu récupérer votre géolocalisation et vous n'avez pas renseigné d'adresse dans votre profil. Au moins l'une des deux informations est nécessaire pour pouvoir déposer un repas sur Don'Eat");
                return $this->redirectToRoute('app_profile');
            }

            $meal->setLocation($location);

            $meal
                ->setCreatedBy($currentUser)
                ->setCreatedAt(new DateTimeImmutable());

            $entityManager->persist($meal);
            $entityManager->flush();

            $this->addFlash('success', "Votre repas a bien été enregistrée!");
            return $this->redirectToRoute('app_meal');
        }
        
        return $this->render('meal/create.html.twig', [
            'createForm' => $createForm
        ]);
    }

    #[Route('/meal/{id<\d+>}/update', name: 'app_meal_update', methods: ['GET', 'POST'])]
    #[IsGranted('edit', 'meal')]
    public function update(Meal $meal, FileUploader $fileUploader, Request $request, 
        EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {  
        $updateForm = $this->createForm(MealUpdateForm::class, $meal);
        $deleteForm = $this->createForm(MealDeleteForm::class, $meal, [
            'action'    => $this->generateUrl('app_meal_delete', ['id' => $meal->getId()])
        ]);

        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid())
        {
            try {

                $picture = $updateForm->get('imageFile')->getData();
                if($picture) {

                    $currentPictureFilename = $meal->getPicture();

                    // Si une image existe déjà, je la supprime
                    if($currentPictureFilename) {
                        $fileUploader->remove($currentPictureFilename);

                        $newFilename = $fileUploader->upload($picture); //< Utilisation du service
                        $meal->setPicture($newFilename);
                    }
                }
            }
            catch(FileException $e) {

                $logger->error("UPDATE MEAL ERROR" . $e->getMessage());
            } 

            $entityManager->flush();
            return $this->redirectToRoute('app_meal');
        }

        return $this->render('meal/update.html.twig', [
            'updateForm'    => $updateForm,
            'deleteForm'    => $deleteForm
        ]);
    }

    #[Route('/meal/{id<\d+>}/delete', name: 'app_meal_delete', methods: ['POST'])]
    #[IsGranted('edit', 'meal')]
    public function delete(Meal $meal, FileUploader $fileUploader, Request $request, 
        EntityManagerInterface $entityManager, LoggerInterface $logger): Response
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
}
