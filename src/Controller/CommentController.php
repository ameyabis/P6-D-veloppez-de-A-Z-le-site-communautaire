<?php

namespace App\Controller;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }
    
    #[Route('/comment', name: 'app_comment')]
    public function index(): Response
    {
        //ajouter la variable de l'id trick
        // $this->getCommentsForPost(2);

        return $this->render('comment/index.html.twig', [
            'controller_name' => 'CommentController',
        ]);
    }

    // public function addComment(): Comment
    // {

    // }
}
