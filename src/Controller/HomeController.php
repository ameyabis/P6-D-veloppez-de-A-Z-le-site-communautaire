<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $tricks = new TrickController($this->em);
        $arrayTricks = $tricks->getTricks();

        return $this->render('home/homePage.html.twig', [
            'tricks' => $arrayTricks,
        ]);
    }
}
