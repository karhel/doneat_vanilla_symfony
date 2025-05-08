<?php

namespace App\Controller;

use App\Entity\Meal;
use DateTimeImmutable;
use App\Entity\MealBookRequest;
use App\Form\CloseMealBookRequestForm;
use App\Form\CreateMealBookRequestForm;
use App\Form\DeleteMealBookRequestForm;
use App\Form\RefuseMealBookRequestForm;
use App\Form\UpdateMealBookRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ValidateMealBookRequestForm;
use App\Repository\MealBookRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookRequestController extends AbstractController
{
    #[Route('/book/request', name: 'app_book_request')]
    public function index(MealBookRequestRepository $mealBookRequestRepository): Response
    {
        $bookRequests = $mealBookRequestRepository->findByRequestedBy($this->getUser());
        $bookReceived = $mealBookRequestRepository->findByCreatedBy($this->getUser());

        return $this->render('book_request/index.html.twig', [
            'bookRequests'  => $bookRequests,
            'bookReceived'  => $bookReceived
        ]);
    }

    #[Route('/meal/book/{id<\d+>}', name: 'app_meal_book_request', methods: ['GET', 'POST'])]
    #[IsGranted('book', subject: 'meal', message: "Vous ne pouvez pas réserver ce repas")]
    public function create(Request $request, Meal $meal, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $bookRequest = new MealBookRequest();
        $bookRequest->setMeal($meal);
        $bookRequest->setRequestedBy($this->getUser());

        $form = $this->createForm(CreateMealBookRequestForm::class, $bookRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            $bookRequest->setRequestedAt(new \DateTimeImmutable())
                ->setRequestedBy($this->getUser())
                ->setIsClosed(false)
                ->setStatus(0);

            $entityManager->persist($bookRequest);
            $entityManager->flush();

            $this->addFlash("success", "Demande de réservation envoyée. Vous pouvez la consulter à partir de votre profile > Mes demandes");
            return $this->redirectToRoute('app_meal');
        }

        return $this->render('book_request/create.html.twig', [
            'mealBookForm' => $form
        ]);
    }
    
    #[Route('/meal/book/edit/{id<\d+>}', name: 'app_meal_book_edit', methods: ['GET', 'POST'])]
    //#[IsGranted('edit_book', subject: 'meal', message: "Vous ne pouvez pas modifier la réservation de ce repas")]
    public function edit(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager): Response
    {
        $editForm   = $this->createForm(UpdateMealBookRequestForm::class, $mealbookRequest);

        $deleteForm = $this->createForm(DeleteMealBookRequestForm::class, $mealbookRequest, [
            'action'    => $this->generateUrl('app_meal_book_delete', ['id' => $mealbookRequest->getId()])
        ]);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();
            $this->addFlash("success", "Les informations ont été enregistrées");
            return $this->redirectToRoute('app_meal_book_edit', ['id' => $mealbookRequest->getId()]);
        }

        return $this->render('book_request/edit.html.twig', [
            'bookRequest'               => $mealbookRequest,
            'updateMealBookRequestForm' => $editForm,
            'deleteMealBookRequestForm' => $deleteForm
        ]);
    }
    
    #[Route('/meal/book/delete/{id<\d+>}', name: 'app_meal_book_delete', methods: ['GET', 'POST'])]
    //#[IsGranted('validate_book', subject: 'meal', message: "Vous ne pouvez pas supprimer la réservation de ce repas")]
    public function delete(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($mealbookRequest);
        $entityManager->flush();

        return $this->redirectToRoute('app_meal');
    }
    
    #[Route('/meal/book/validate/{id<\d+>}', name: 'app_meal_book_validate', methods: ['GET', 'POST'])]
    //#[IsGranted('validate_book', subject: 'meal', message: "Vous ne pouvez pas valider la réservation de ce repas")]
    public function validate(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ValidateMealBookRequestForm::class, $mealbookRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Symfony\Component\Form\Button $btnValidate */
            $btnValidate = $form->get('validate');
            
            /** @var Symfony\Component\Form\Button $btnRefuse */
            $btnRefuse   = $form->get('refuse');

            if($btnValidate->isClicked()) {
                
                $mealbookRequest->setValidatedAt(new \DateTimeImmutable())
                    ->setStatus(1);
            }
            elseif($btnRefuse->isClicked()) {
                
                $mealbookRequest->setValidatedAt(new \DateTimeImmutable())
                    ->setStatus(2)
                    ->setisClosed(true);                
            }

            $entityManager->flush();
            $this->addFlash("success", "La réponse a été envoyée");

            return $this->redirectToRoute("app_book_request");
        }

        return $this->render('book_request/validate.html.twig', [
            'bookRequest'   => $mealbookRequest,
            'validateMealBookRequestForm' => $form
        ]);
    }
    
    #[Route('/meal/book/close/{id<\d+>}', name: 'app_meal_book_close', methods: ['GET', 'POST'])]
    //#[IsGranted('validate_book', subject: 'meal', message: "Vous ne pouvez pas clore la réservation de ce repas")]
    public function close(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UpdateMealBookRequestForm::class, $mealbookRequest);

        return $this->redirectToRoute('app_meal_book_close', ['id' => $mealbookRequest->getId()]);
    }
}
