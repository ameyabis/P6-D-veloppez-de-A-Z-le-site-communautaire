<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Service\DateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CommentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DateService $dateService
    ) {
    }
    public function getComment(int $id): array
    {
        $comments = $this->em->getRepository(Comment::class)->findBy(
            ['trick' => $id],
            ['id' => 'DESC'],
            5
        );

        return $comments;
    }

    public function addComment(
        Request $request,
        ?User $user,
        Trick $trick
    ): FormInterface
    {
        $comment = new Comment();
        $dateNow = $this->dateService->dateNow();

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $completedForm = $request->request->all()['comment'];
            $comment->setContent($completedForm['content']);
            $comment->setUser($user);
            $comment->setTrick($trick);
            $comment->setDateCreate($dateNow);

            $this->em->persist($comment);
            $this->em->flush();

            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForm->get('content')->setData(null);
        }

        return $commentForm;
    }
}
