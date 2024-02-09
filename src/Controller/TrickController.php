<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Form\CreateTrickType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }

    #[Route(path: '/formTrick', name: 'form_trick')]
    public function createFormTrick(
        Request $request,
    #[CurrentUser] ?User $user
    ): Response {
        $trick = new Trick();
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $formTrick = $this->createForm(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()["create_trick"];

            $video = new Video();
            $video->setUrl($completedForm['videos']['url']);

            $trick->setName($completedForm['name']);
            $trick->setGroupTrick($completedForm['groupTrick']);
            $trick->setDescription($completedForm['description']);
            $trick->setDateCreate($dateNow);
            $trick->setUser($user);
            $trick->addVideo($video);

            $this->em->persist($video);
            $this->em->persist($trick);
            $this->em->flush();
            // $id = $trick->getId();
            // dd($id);

            return $this->render('home/homePage.html.twig', [
                'tricks' => $this->getTricks()
            ]);
        } else {
            return $this->render('crud/formCreateTrick.html.twig', [
                'formTrick' => $formTrick,
            ]);
        }
    }

    #[Route(path: '/trick/{id}', name: 'one_trick')]
    public function showOneTrick(
        int $id,
        Request $request,
    #[CurrentUser] ?User $user,
    ): Response {
        $trick = $this->em->getRepository(Trick::class)->find($id);
        $comments = $this->em->getRepository(Comment::class)->findBy(['trick' => $id]);

        $comment = new Comment();

        $commentForm = $this->createForm(CommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $completedForm = $request->request->all()['comment'];
            $comment->setContent($completedForm['content']);
            $comment->setUser($user);
            $comment->setTrick($trick);

            $this->em->persist($comment);
            $this->em->flush();
        }

        return $this->render('page/trick.html.twig', [
            'trick' => $trick,
            'comments' => $comments,
            'formComment' => $commentForm,
        ]);
    }

    public function getTricks(): array
    {
        $tricks = $this->em->getRepository(Trick::class)->findAll();

        return $tricks;
    }

    #[Route(path: '/tricks', name: 'all_tricks')]
    public function showTricks(): Response
    {
        $tricks = $this->em->getRepository(Trick::class)->findAll();

        return $this->render('page/tricks.html.twig', [
            'tricks' => $tricks,
        ]);
    }

    #[Route(path: '/deleteTrick/{id}', name: 'delete_trick')]
    public function deleteTrick(int $id): Response  //Response
    {
        $trick = $this->em->getRepository(Trick::class)->find($id);

        $this->em->remove($trick);
        $this->em->flush();

        return $this->redirectToRoute('app_home');
    }

    #[Route(path: '/editTrick/{id}', name: 'edit_trick')]
    public function editTrick(
        int $id,
        Request $request
    ): Response {
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $trick = $this->em->getRepository(Trick::class)->find($id);

        $formTrick = $this->createForm(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()["create_trick"];
            $trick->setName($completedForm['name']);
            $trick->setGroupTrick($completedForm['groupTrick']);
            $trick->setDescription($completedForm['description']);
            $trick->setDateEdit($dateNow);

            $this->em->persist($trick);
            $this->em->flush();

            return $this->render('home/homePage.html.twig', [
                'tricks' => $this->getTricks()
            ]);
        } else {
            return $this->render('crud/formEditTrick.html.twig', [
                'formTrick' => $formTrick->createView(),
            ]);
        }

    }
}
