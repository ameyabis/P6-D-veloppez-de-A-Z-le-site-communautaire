<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Service\JwtService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $em,
        Security $security,
        SendMailService $mail,
        JwtService $jwt,
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $em->persist($user);
            $em->flush();

            $header = [
                'type' => 'JWT',
                'alg' => 'HS256'
            ];

            $payload = [
                'user_id' => $user->getId()
            ];

            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            $mail->send(
                'jg@a-w-c.eu',
                $user->getEmail(),
                'Activation de votre compte sur le site Snowtricks',
                'register',
                compact('user', 'token'),
            );

            $security->login($user);

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/{token}', name: 'verify_user')]

    public function verifyUser(
        string $token,
        JwtService $jwt,
        UserRepository $userRepository,
        EntityManagerInterface $em,
    ): Response {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);

            if ($user && !$user->isVerified()) {
                $user->setIsVerified(true);
                $em->flush();

                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_home');
            }
        }

        $this->addFlash('danger', 'Le token est invalide ou a expiré');

        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(
        JwtService $jwt,
        SendMailService $sendMail,
        UserRepository $userRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page');
            return $this->redirectToRoute('app_login');
        }

        if ($user->isVerified()) {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('app_home');
        }

        $header = [
            'type' => 'JWT',
            'alg' => 'HS256'
        ];

        $payload = [
            'user_id' => $user->getId()
        ];

        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        $sendMail->send(
            'jg@a-w-c.eu',
            $user->getEmail(),
            'Activation de votre compte sur le site Snowtricks',
            'register',
            compact('user', 'token'),
        );

        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('app_home');
    }
}
