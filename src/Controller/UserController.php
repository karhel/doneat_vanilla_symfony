<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UpdateUserForm;
use App\Repository\UserRepository;
use App\Form\UpdateUserPasswordForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserController extends AbstractController
{
    #[Route('/admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $editForm = $this->createForm(UpdateUserForm::class, $user);
        $pwdForm = $this->createForm(UpdateUserPasswordForm::class, $user);

        $editForm->handleRequest($request);
        $pwdForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $entityManager->flush();            
            $this->addFlash('success', "Vos informations ont été mises à jour");

            return $this->redirectToRoute('app_profile');
        }
        else if ($pwdForm->isSubmitted() && $pwdForm->isValid()) {

            $currentPassword    = $pwdForm->get('currentPassword')->getData();
            $newPassword        = $pwdForm->get('plainPassword')->getData();

            if($userPasswordHasher->isPasswordValid($user, $currentPassword)) {
                
                // encode the plain password
                $user->setPassword($userPasswordHasher->hashPassword($user, $newPassword));
                $entityManager->flush();

                $this->addFlash('success', "Votre mot de passe a été mis à jour");
                
                return $this->redirectToRoute('app_profile');
            }
            else {

                // Le mot de passe actuel n'est pas le bon
                $error = new FormError("Le mot de passe saisi ne correspond pas à votre mot de passe actuel");
                $pwdForm->addError($error);
            }
        }

        return $this->render('user/profile.html.twig', [
            'user'                  => $user,
            'updateUserInfo'        => $editForm,
            'updateUserPassword'    => $pwdForm
        ]);
    }

    #[Route('/admin/user/edit/{id<\d>}', name: 'app_user_edit')]
    public function edit(User $user, EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/edit.html.twig', [
            'user'  => $user
        ]);
    }

    #[Route('/admin/user/delete/{id<\d>}', name: 'app_user_delete')]
    public function delete(User $user, EntityManagerInterface $entityManager): Response
    {
        return $this->render('user/delete.html.twig', [
            'user'  => $user
        ]);
    }
}
