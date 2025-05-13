<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Address;
use App\Form\DeleteUserForm;
use App\Form\UpdateUserForm;
use App\Form\UserUpdateForm;
use App\Form\UserAddressForm;
use App\Form\UserPasswordForm;
use App\Form\UpdateUserRolesForm;
use App\Service\GeocodingService;
use App\Repository\UserRepository;
use App\Form\UpdateUserAddressForm;
use App\Form\UpdateUserPasswordForm;
use Symfony\Component\Form\FormError;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher, GeocodingService $geocodingService): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED');

        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $mainAddress = $currentUser->getMainAddress() ?: new Address();

        $updateForm  = $this->createForm(UserUpdateForm::class, $currentUser);
        $passwdForm  = $this->createForm(UserPasswordForm::class, $currentUser);
        $addresForm  = $this->createForm(UserAddressForm::class, $mainAddress);

        $updateForm->handleRequest($request);
        $passwdForm->handleRequest($request);
        $addresForm->handleRequest($request);

        if ($updateForm->isSubmitted() && $updateForm->isValid()) {

            $entityManager->flush();

            $this->addFlash('success', "Vos informations ont été mises à jour");

            return $this->redirectToRoute('app_profile');


        } elseif ($addresForm->isSubmitted() && $addresForm->isValid()) {

            if(!$mainAddress->getId()) { 

                $entityManager->persist($mainAddress); 

                $currentUser->setMainAddress($mainAddress);
            }

            $entityManager->flush();

            $this->addFlash('success', "Votre adresse a été enregistrée");

            return $this->redirectToRoute('app_profile');

        } elseif ($passwdForm->isSubmitted() && $passwdForm->isValid()) {

            $currentPassword    = $passwdForm->get('currentPassword')->getData();
            $newPassword        = $passwdForm->get('plainPassword')->getData();

            if($userPasswordHasher->isPasswordValid($currentUser, $currentPassword)) {

                // encode the plain password
                $currentUser->setPassword($userPasswordHasher->hashPassword($currentUser, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', "Votre mot de passe a été mis à jour");

                return $this->redirectToRoute('app_profile');

            } else {

                // Le mot de passe actuel n'est pas le bon
                $error = new FormError("Le mot de passe saisi ne correspond pas à votre mot de passe actuel");
                $passwdForm->addError($error);
            }

        }

        return $this->render('user/profile.html.twig', [
            'user'          => $currentUser,
            'updateForm'    => $updateForm,
            'passwdForm'    => $passwdForm,
            'addresForm'    => $addresForm
        ]);
    }

}
