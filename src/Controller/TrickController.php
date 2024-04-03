<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Form\CommentType;
use App\Form\CreateTrickType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class TrickController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/formTrick', name: 'form_trick')]
    public function createFormTrick(
        Request $request,
        ParameterBagInterface $params,
    #[CurrentUser] ?User $user
    ): Response {
        $trick = new Trick();
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $formTrick = $this->createForm(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()['create_trick'];
            $pictures = $request->files->all()['create_trick']['pictures'];

            $trick->setName($completedForm['name']);
            $trick->setGroupTrick($completedForm['groupTrick']);
            $trick->setDescription($completedForm['description']);
            $trick->setDateCreate($dateNow);
            $trick->setUser($user);

            $this->em->persist($trick);

            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);

                $this->em->persist($video);
            }

            foreach ($pictures as $pictureUpload) {
                $extension = $pictureUpload->guessExtension();
                $path = $params->get('images_directory');
                $fichier = $pictureUpload->getFileName();

                $picture = new Picture();
                $picture->setUrl($fichier . '.' . $extension);
                $picture->setTrick($trick);

                $this->em->persist($picture);
                $pictureUpload->move($path . '/', $fichier . '.' . $extension);
            }

            $this->em->flush();

            return $this->render('home/homePage.html.twig', [
                'tricks' => $this->getTricks()
            ]);
        } else {
            return $this->render('crud/formTrick.html.twig', [
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
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $trick = $this->em->getRepository(Trick::class)->find($id);
        $videos = $this->em->getRepository(Video::class)->findBy(['trick' => $id]);
        foreach ($videos as $video) {
            $video->setType('video');
        }

        $pictures = $this->em->getRepository(Picture::class)->findBy(['trick' => $id]);
        foreach ($pictures as $picture) {
            $picture->setType('picture');
        }

        $attachments = array_merge($videos, $pictures);

        $comment = new Comment();

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

        $comments = $this->em->getRepository(Comment::class)->findBy(
            ['trick' => $id],
            ['id' => 'DESC'],
            5
        );


        return $this->render('page/trick.html.twig', [
            'trick' => $trick,
            'attachments' => $attachments,
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

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/deleteTrick/{id}', name: 'delete_trick')]
    public function deleteTrick(int $id): Response  //Response
    {
        $trick = $this->em->getRepository(Trick::class)->find($id);

        $this->em->remove($trick);
        $this->em->flush();

        return $this->redirectToRoute('app_home');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/editTrick/{id}', name: 'edit_trick')]
    public function editTrick(
        int $id,
        Request $request,
        ParameterBagInterface $params,
    ): Response {
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $trick = $this->em->getRepository(Trick::class)->find($id);

        $formTrick = $this->createForm(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()["create_trick"];
            $pictures = $request->files->all()['create_trick']['pictures'];

            $trick->setName($completedForm['name']);
            $trick->setGroupTrick($completedForm['groupTrick']);
            $trick->setDescription($completedForm['description']);
            $trick->setDateEdit($dateNow);

            $this->em->persist($trick);

            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);

                $this->em->persist($video);
            }

            foreach ($pictures as $pictureUpload) {
                $extension = $pictureUpload->guessExtension();
                $path = $params->get('images_directory');
                $fichier = $pictureUpload->getFileName();

                $picture = new Picture();
                $picture->setUrl($fichier . '.' . $extension);
                $picture->setTrick($trick);

                $this->em->persist($picture);
                $pictureUpload->move($path . '/', $fichier . '.' . $extension);
            }

            $this->em->flush();

            return $this->render('home/homePage.html.twig', [
                'tricks' => $this->getTricks()
            ]);
        } else {
            return $this->render('crud/formTrick.html.twig', [
                'formTrick' => $formTrick->createView(),
            ]);
        }

    }
}
