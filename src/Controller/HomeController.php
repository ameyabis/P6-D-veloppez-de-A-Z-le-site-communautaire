<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Entity\Groups;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em
    ) {
    }

    #[Route(path: '/', name: 'app_home', methods: 'GET')]
    public function index(
        Request $request,
        TrickRepository $trickRepository
    ): Response {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $trickRepository->getTricksPaginator($offset);

        $groups = $this->em->getRepository(Groups::class)->findAll();

        return $this->render('home/homepage.html.twig', [
            'tricks' => $paginator,
            'previous' => $offset - TrickRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + TrickRepository::PAGINATOR_PER_PAGE),
            'groups' => $groups,
        ]);
    }
}
