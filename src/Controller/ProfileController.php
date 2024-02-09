<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }
    
    #[Route('/profile', name: 'profile')]
    public function showProfile(
    #[CurrentUser] ?User $user
    ): Response {
        dd($user);

        return $this->render('page/profile.html.twig', [
            'user' => $user,
        ]);
    }
}
