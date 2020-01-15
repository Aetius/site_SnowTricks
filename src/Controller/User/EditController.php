<?php

namespace App\Controller\User;

use App\Entity\EmailLinkToken;
use App\Entity\User;
use App\Form\DTO\EditUserDTO;
use App\Form\User\EditUserType;
use App\Form\User\LostPasswordType;
use App\Form\User\NewPasswordType;
use App\Form\User\RegistrationUserType;
use App\Notification\EmailNotification;
use App\Repository\EmailLinkTokenRepository;
use App\Repository\EmailRepository;
use App\Repository\UserRepository;
use App\Security\TokenEmail;
use App\Services\Email\Email;
use App\Services\User\EditorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;


class EditController extends AbstractController
{
    /**
     * @Route ("/inscription", name="user_new", methods={"GET|POST"})
     */
    public function new(Request $request, EditorService $userCreator, EmailNotification $notification)
    {
        $form = $this->createForm(RegistrationUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userCreator->create($form->getData());
            $notification->confirmEmail($user);
            $this->addFlash('success', "flash.registration.success");
            $this->Login($user);
            return $this->redirectToRoute('home');
        }
        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
            'errors' => $form->getErrors(true)
        ]);
    }

    /**
     * @Route ("/user/confirm_email/{token}", name="confirm_email",  methods={"GET"})
     */
    public function confirmEmail(EmailLinkToken $emailLinkToken, User $user, Email $emailSevice, Request $request)
    {
        if ($emailSevice->validationEmail($user) === true) {
            $this->addFlash('success', "L'email a bien été enregistré");
        } else {
            $this->addFlash('danger', "L'adresse email n'a pu être enregistrée. Veuillez Réessayer!");
        }
        return $this->redirectToRoute('home');
    }

    /**
     * @Route ("/profile", name="user_update", methods={"GET|POST"})
     */
    public function update(Request $request, EditorService $userUpdate, UserRepository $repository)
    {
        /* $email = ($emailUser->findOneBy([
             'user'=>$this->getUser()->getId()
         ]));*/

        $user = $repository->findOneBy(['login' => $this->getUser()->getLogin()]);

        $form = $this->createForm(EditUserType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $userUpdate->update($this->getUser(), $form->getData());
            $this->addFlash('success', "Modifications effectuées");
        }
        return $this->render('user/update.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    /**
     * @Route ("/password_lost", name="user_password_lost", methods={"GET|POST"})
     */
    public function lostPassword(Request $request, EmailNotification $emailNotification, UserRepository $userRepository,
                                 TokenEmail $token, EditorService $editor)
    {
        $form = $this->createForm(LostPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($user = $userRepository->findOneBy(['login' => $form->getData()->login])) {
                $editor->resetPassword($user);
            }
            $this->addFlash('success', "Demande effectuée");
        }
        return $this->render('user/password_lost.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route ("/user/password_reset/{token}", name="user_password_reset",  methods={"GET|POST"})
     */
    public function resetPassword(EmailLinkToken $emailLinkToken, User $user, Request $request,
                                  Email $emailSevice, EditorService $userEditor)
    {
        if ($emailSevice->lostPassword($user) === true) {
            $form = $this->createForm(NewPasswordType::class);
            $form->handleRequest($request);

            if (!($form->isSubmitted() && $form->isValid())) {
                return $this->render('user/new_password.html.twig', [
                    'form' => $form->createView()
                ]);
            }
            $userEditor->update($user, $form->getData());
            $this->Login($user);
        }
        return $this->redirectToRoute('home');
    }


    protected function Login($user)
    {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
        $this->container->get('session')->set('_security_main', serialize($token));
    }


}