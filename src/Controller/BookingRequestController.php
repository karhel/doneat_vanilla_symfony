<?php

namespace App\Controller;

use App\Entity\BookingRequest;
use App\Form\BookingRequestCreateForm;
use App\Form\BookingRequestDeleteForm;
use App\Form\BookingRequestUpdateForm;
use App\Form\BookingRequestValidateForm;
use App\Repository\BookingRequestRepository;
use App\Repository\MealRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookingRequestController extends AbstractController
{
    #[Route('/booking/request', name: 'app_booking_request', methods: ['GET'])]
    public function index(BookingRequestRepository $bookingRepository): Response
    {        
        return $this->render('booking_request/index.html.twig', [

            'pendingRequests'   => $bookingRepository->findByStatusAndRequestedBy($this->getUser(), BookingRequest::STATUS_PENDING),

            'pendingApproval'   => $bookingRepository->findByStatusAndMealCreatedBy($this->getUser(), BookingRequest::STATUS_PENDING),

            'pendingClosureByGiver'      => $bookingRepository->findByCreatedByAndToClose($this->getUser()),

            'pendingClosureByEater'      => $bookingRepository->findByRequestedByAndToClose($this->getUser())
        ]);
    }

    #[Route('/booking/archives', name: 'app_booking_archives', methods: ['GET'])]
    public function archives(BookingRequestRepository $bookingRepository): Response
    {
        return $this->render('booking_request/archives.html.twig', [
            
            'refusedByMe'       => $bookingRepository->findByStatusAndMealCreatedBy($this->getUser(), BookingRequest::STATUS_REFUSED, true),
            'refusedToMe'       => $bookingRepository->findByStatusAndRequestedBy($this->getUser(), BookingRequest::STATUS_REFUSED, true),

            'closedByMe'        => $bookingRepository->findByStatusAndMealCreatedBy($this->getUser(), BookingRequest::STATUS_VALIDATED, true),
            'closedToMe'        => $bookingRepository->findByStatusAndRequestedBy($this->getUser(), BookingRequest::STATUS_VALIDATED, true)
        ]);
    }

    #[Route('/booking/request/create', name: 'app_booking_create', methods: ['GET', 'POST'])]
    public function create(Request $request, MealRepository $mealRepository, EntityManagerInterface $entityManager): Response
    {
        $meal = $mealRepository->findOneById($request->query->get('meal_id'));
        if(!$meal) {

            $this->addFlash('error', "Le repas que vous souhaitez réserver n'est pas disponible");
            return $this->redirectToRoute('app_meal');
        }

        $bookingRequest = new BookingRequest();
        $bookingRequest->setMeal($meal);

        $bookingRequest->setRequestedBy($this->getUser());

        $createForm = $this->createForm(BookingRequestCreateForm::class, $bookingRequest);

        $createForm->handleRequest($request);

        if($createForm->isSubmitted() && $createForm->isValid()) {

            $bookingRequest
                ->setRequestedAt(new DateTimeImmutable())
                ->setClosedAt(null)
                ->setStatus(BookingRequest::STATUS_PENDING);

            $entityManager->persist($bookingRequest);
            $entityManager->flush();

            // TODO envoi du mail d'information qu'une demande de réservation a été faite

            $this->addFlash('success', "Votre demande a été envoyée");
            return $this->redirectToRoute('app_meal');
        }

        return $this->render('booking_request/create.html.twig', [
            'meal'          => $meal,
            'createForm'    => $createForm
        ]);
    }

    #[Route('/booking/request/{id<\d+>}/update', name: 'app_booking_update', methods: ['GET', 'POST'])]
    public function update(BookingRequest $bookingRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $updateForm = $this->createForm(BookingRequestUpdateForm::class, $bookingRequest);

        $deleteForm = $this->createForm(BookingRequestDeleteForm::class, $bookingRequest, [
            'action'    => $this->generateUrl('app_booking_delete', ['id' => $bookingRequest->getId()])
        ]);

        $updateForm->handleRequest($request);

        if($updateForm->isSubmitted() && $updateForm->isValid()) {

            $entityManager->flush();

            // TODO créer le mail

            $this->addFlash('success', "La demande de réservation a été mise à jour");

            return $this->redirectToRoute('app_booking_request');
        }

        return $this->render('booking_request/update.html.twig', [
            'bookingRequest'    => $bookingRequest,
            'updateForm'        => $updateForm,
            'deleteForm'        => $deleteForm
        ]);
    }

    #[Route('/booking/request/{id<\d+>}/validate', name: 'app_booking_validate')]
    public function validate(BookingRequest $bookingRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $validateForm = $this->createForm(BookingRequestValidateForm::class, $bookingRequest);
        $validateForm->handleRequest($request);

        if($validateForm->isSubmitted() && $validateForm->isValid()) {
            /** @var Symfony\Component\Form\Button $btnValidate */
            $btnValidate = $validateForm->get('validate');
            
            /** @var Symfony\Component\Form\Button $btnRefuse */
            $btnRefuse   = $validateForm->get('refuse');

            if($btnValidate->isClicked()) {

                $bookingRequest
                    ->setValidatedAt(new DateTimeImmutable())
                    ->setStatus(BookingRequest::STATUS_VALIDATED);
                

                // TODO Le mail
                    
            }
            elseif($btnRefuse->isClicked()) {
                $date = new DateTimeImmutable();

                $bookingRequest
                    ->setValidatedAt($date)
                    ->setClosedAt($date)
                    ->setStatus(BookingRequest::STATUS_REFUSED);                

                // TODO Le mail

            }

            $entityManager->flush();
            $this->addFlash("success", "La réponse a été envoyée");

            return $this->redirectToRoute("app_booking_request");
        }

        return $this->render('booking_request/validate.html.twig', [
            'bookingRequest'    => $bookingRequest,
            'validateForm'      => $validateForm
        ]);
    }

    #[Route('/booking/request/{id<\d+>}/close/eater', name: 'app_booking_close_eater')]
    public function closeByEater(BookingRequest $bookingRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookingRequest->setClosedByEaterAt(new DateTimeImmutable());
        $entityManager->flush();

        // TODO le mail

        $this->addFlash('success', "La réservation a été cloturée avec succès");

        $this->checkClose($bookingRequest, $entityManager);

        return $this->redirectToRoute('app_meal');
    }

    #[Route('/booking/request/{id<\d+>}/close/giver', name: 'app_booking_close_giver')]
    public function closeByGiver(BookingRequest $bookingRequest, Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookingRequest->setClosedByGiverAt(new DateTimeImmutable());
        $entityManager->flush();

        // TODO le mail

        $this->addFlash('success', "La réservation a été cloturée avec succès");

        $this->checkClose($bookingRequest, $entityManager);

        return $this->redirectToRoute('app_meal');
    }

    private function checkClose(BookingRequest $bookingRequest, EntityManagerInterface $entityManager)
    {
        if($bookingRequest->getClosedByEaterAt() && $bookingRequest->getClosedByGiverAt())
        {
            $bookingRequest->setClosedAt(new DateTimeImmutable());
            $entityManager->flush();
        }
    }

    #[Route('/booking/request/{id<\d+>}/delete', name: 'app_booking_delete')]
    public function delete(BookingRequest $bookingRequest, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($bookingRequest);
        $entityManager->flush();
        
        // TODO créer le mail

        $this->addFlash('success', "Votre demande a été annulée");
        return $this->redirectToRoute('app_meal');
    }
}
