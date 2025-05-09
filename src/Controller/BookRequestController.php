<?php

namespace App\Controller;

use App\Entity\Meal;
use DateTimeImmutable;
use App\Entity\MealBookRequest;
use App\Form\CloseMealBookRequestForm;
use Symfony\Component\Mime\Address;
use App\Form\CreateMealBookRequestForm;
use App\Form\DeleteMealBookRequestForm;
use App\Form\RefuseMealBookRequestForm;
use App\Form\UpdateMealBookRequestForm;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ValidateMealBookRequestForm;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Repository\MealBookRequestRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BookRequestController extends AbstractController
{
    #[Route('/book/request', name: 'app_book_request')]
    public function index(MealBookRequestRepository $mealBookRequestRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $requestedPending   = $mealBookRequestRepository->findByStatusAndRequestedBy($this->getUser(), MealBookRequest::STATUS_PENDING);
        $requestedValidated = $mealBookRequestRepository->findByStatusAndRequestedBy($this->getUser(), MealBookRequest::STATUS_VALIDATED);

        $receivedPending    = $mealBookRequestRepository->findByStatusAndMealCreatedBy($this->getUser(), MealBookRequest::STATUS_PENDING);
        $receivedValidated  = $mealBookRequestRepository->findByStatusAndMealCreatedBy($this->getUser(), MealBookRequest::STATUS_VALIDATED);

        return $this->render('book_request/index.html.twig', [
            'requestedPending'      => $requestedPending,
            'requestedValidated'    => $requestedValidated,

            'receivedPending'       => $receivedPending,
            'receivedValidated'     => $receivedValidated,
        ]);
    }

    #[Route('/book/request/archives', name: 'app_book_request_archives')]
    public function archives(MealBookRequestRepository $mealBookRequestRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        $requestedRefused   = $mealBookRequestRepository->findByStatusAndRequestedBy($this->getUser(), MealBookRequest::STATUS_REFUSED, true);
        $requestedClosed    = $mealBookRequestRepository->findByStatusAndRequestedBy($this->getUser(), MealBookRequest::STATUS_VALIDATED, false);

        $receivedRefused    = $mealBookRequestRepository->findByStatusAndMealCreatedBy($this->getUser(), MealBookRequest::STATUS_REFUSED, true);
        $receivedClosed     = $mealBookRequestRepository->findByStatusAndMealCreatedBy($this->getUser(), MealBookRequest::STATUS_VALIDATED, true);

        return $this->render('book_request/archives.html.twig', [
            'requestedRefused'      => $requestedRefused,
            'requestedClosed'       => $requestedClosed,

            'receivedRefused'       => $receivedRefused,
            'receivedClosed'        => $receivedClosed
        ]);
    }

    

    #[Route('/meal/book/{id<\d+>}', name: 'app_meal_book_request', methods: ['GET', 'POST'])]
    #[IsGranted('book', subject: 'meal', message: "Vous ne pouvez pas réserver ce repas")]
    public function create(Request $request, Meal $meal, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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
                ->setStatus(MealBookRequest::STATUS_PENDING);

            $entityManager->persist($bookRequest);
            $entityManager->flush();

            // TODO : Envoyer un mail ici à la personne ayant posté le repas
            $email = (new TemplatedEmail())
                ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
                ->to((string) $meal->getCreatedBy()->getEmail())
                ->subject("Une demande a été faite pour votre repas")
                ->htmlTemplate('book_request/email_request_giver.html.twig')
                ->context([
                    'mealRequest' => $bookRequest,
                    'meal'  => $meal
                ])
            ;

            $mailer->send($email);

            // TODO : Envoyer un mail ici de confirmation de demande de réservation à la personne ayant fait la demande
            $email = (new TemplatedEmail())
                ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
                ->to((string) $bookRequest->getRequestedBy()->getEmail())
                ->subject("Votre demande pour le repas a été envoyée")
                ->htmlTemplate('book_request/email_request_eater.html.twig')
                ->context([
                    'mealRequest' => $bookRequest,
                    'meal'  => $meal                    
                ])
            ;

            $mailer->send($email);

            $this->addFlash("success", "Demande de réservation envoyée. Vous pouvez la consulter à partir de votre profile > Mes demandes");
            return $this->redirectToRoute('app_meal');
        }

        return $this->render('book_request/create.html.twig', [
            'mealBookForm' => $form
        ]);
    }
    
    #[Route('/meal/book/edit/{id<\d+>}', name: 'app_meal_book_edit', methods: ['GET', 'POST'])]
    //#[IsGranted('edit_book', subject: 'meal', message: "Vous ne pouvez pas modifier la réservation de ce repas")]
    public function edit(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $editForm   = $this->createForm(UpdateMealBookRequestForm::class, $mealbookRequest);

        $deleteForm = $this->createForm(DeleteMealBookRequestForm::class, $mealbookRequest, [
            'action'    => $this->generateUrl('app_meal_book_delete', ['id' => $mealbookRequest->getId()])
        ]);

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $entityManager->flush();

            // TODO : Envoyer un mail aux deux personnes (demandeur et personne ayant posté le repas) qu'une mise à jour été faite sur la demande de réservation
            $email = (new TemplatedEmail())
                ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
                ->subject("La demande de réservation a été modifiée")
                ->htmlTemplate('book_request/email_request_edit.html.twig')
                ->context([
                    'mealRequest' => $mealbookRequest,
                    'meal'  => $mealbookRequest->getMeal()                
                ])
            ;

            $email->to((string) $mealbookRequest->getRequestedBy()->getEmail());            
            $mailer->send($email);
            
            $email->to((string) $mealbookRequest->getMeal()->getCreatedBy()->getEmail());            
            $mailer->send($email);

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
    public function delete(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $entityManager->remove($mealbookRequest);
        $entityManager->flush();

        // TODO : Envoyer un mail aux deux personnes (demandeur et personne ayant posté le repas) que la demande a été annulée
            $email = (new TemplatedEmail())
            ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
            ->subject("La demande de réservation a été annulée")
            ->htmlTemplate('book_request/email_request_delete.html.twig')
            ->context([
                'mealRequest' => $mealbookRequest,
                'meal'  => $mealbookRequest->getMeal()                
            ])
        ;

        $email->to((string) $mealbookRequest->getRequestedBy()->getEmail());            
        $mailer->send($email);
        
        $email->to((string) $mealbookRequest->getMeal()->getCreatedBy()->getEmail());            
        $mailer->send($email);

        return $this->redirectToRoute('app_meal');
    }
    
    #[Route('/meal/book/validate/{id<\d+>}', name: 'app_meal_book_validate', methods: ['GET', 'POST'])]
    //#[IsGranted('validate_book', subject: 'meal', message: "Vous ne pouvez pas valider la réservation de ce repas")]
    public function validate(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
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
                    ->setStatus(MealBookRequest::STATUS_VALIDATED);

                    // TODO : Envoyer un mail à la personne ayant fait la demande de réservation que celle-ci a été confirmée
                    $email = (new TemplatedEmail())
                        ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
                        ->subject("La demande de réservation a été Valdiée")
                        ->htmlTemplate('book_request/email_request_validated.html.twig')
                        ->context([
                            'mealRequest' => $mealbookRequest,
                            'meal'  => $mealbookRequest->getMeal()                
                        ])
                    ;

                    $email->to((string) $mealbookRequest->getRequestedBy()->getEmail());            
                    $mailer->send($email);
                    
                    $email->to((string) $mealbookRequest->getMeal()->getCreatedBy()->getEmail());            
                    $mailer->send($email);
            }
            elseif($btnRefuse->isClicked()) {
                
                $mealbookRequest->setValidatedAt(new \DateTimeImmutable())
                    ->setStatus(MealBookRequest::STATUS_REFUSED)
                    ->setisClosed(true);          
                    
                    // TODO : Envoyer un mail à la personne ayant fait la demande de réservation que celle-ci a été refusée
                    $email = (new TemplatedEmail())
                    ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
                    ->subject("La demande de réservation a été Refusée")
                    ->htmlTemplate('book_request/email_request_refused.html.twig')
                    ->context([
                        'mealRequest' => $mealbookRequest,
                        'meal'  => $mealbookRequest->getMeal()                
                    ])
                ;

                $email->to((string) $mealbookRequest->getRequestedBy()->getEmail());            
                $mailer->send($email);
                
                $email->to((string) $mealbookRequest->getMeal()->getCreatedBy()->getEmail());            
                $mailer->send($email);
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
    public function close(Request $request, MealBookRequest $mealbookRequest, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        $form = $this->createForm(UpdateMealBookRequestForm::class, $mealbookRequest);

        // TODO : Envoyer un mail à la personne ayant fait la demande de réservation que celle-ci a été cloturée
        $email = (new TemplatedEmail())
            ->from(new Address('contact@don-eat.fr', 'Don-Eat'))
            ->subject("La demande de réservation a été cloturée")
            ->htmlTemplate('book_request/email_request_closed.html.twig')
            ->context([
                'mealRequest' => $mealbookRequest,
                'meal'  => $mealbookRequest->getMeal()                
            ])
        ;

        $email->to((string) $mealbookRequest->getRequestedBy()->getEmail());            
        $mailer->send($email);

        return $this->redirectToRoute('app_meal_book_close', ['id' => $mealbookRequest->getId()]);
    }
}
