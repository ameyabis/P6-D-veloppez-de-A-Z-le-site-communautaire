<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em
    ) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $tricks = $this->em->getRepository(Trick::class)->findAll();

        return $this->render('home/homePage.html.twig', [
            'tricks' => $tricks
        ]);
    }
}
