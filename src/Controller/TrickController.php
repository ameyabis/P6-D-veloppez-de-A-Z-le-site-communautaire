<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Video;
use App\Entity\Comment;
use App\Entity\Picture;
use App\Form\CommentType;
use App\Service\DateService;
use App\Service\FormService;
use App\Form\CreateTrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class TrickController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }

    //Fonction pour créer et modifier notre trick
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/formTrick', name: 'form_trick')]
    public function createFormTrick(
        Request $request,
        ParameterBagInterface $params,
        FormService $formService,
        TrickRepository $trickRepository,
        #[CurrentUser] ?User $user
    ): Response {
        $trick = new Trick();
        $pictures = [];

        return $formService->formDataTrick(
            $trick,
            $user,
            $request,
            $pictures
        );
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/formTrick/{name}', name: 'form_edit')]
    public function editFormTrick(
        string $name,
        Request $request,
        ParameterBagInterface $params,
        FormService $formService,
        TrickRepository $trickRepository,
        #[CurrentUser] ?User $user
    ): Response {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);
        $pictures = $this->em->getRepository(Picture::class)->findBy(['trick' => $trick->getId()]);

        return $formService->formDataTrick(
            $trick,
            $user,
            $request,
            $pictures
        );
    }

    #[Route(path: '/trick/{name}', name: 'one_trick')]
    public function showOneTrick(
        string $name,
        Request $request,
        CommentController $commentController,
        DateService $dateService,
        #[CurrentUser] ?User $user,
    ): Response {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);
        $videos = $this->em->getRepository(Video::class)->findBy(['trick' => $trick->getId()]);
        //Set->type pour différencier les videos des images
        foreach ($videos as $video) {
            $video->setType('video');
        }

        $pictures = $this->em->getRepository(Picture::class)->findBy(['trick' => $trick->getId()]);
        foreach ($pictures as $picture) {
            $picture->setType('picture');
        }

        $attachments = array_merge($videos, $pictures);

        $commentForm = $commentController->addComment(
            $request,
            $user,
            $trick
        );

        $comments = $commentController->getComment($trick->getId());

        return $this->render('page/trick.html.twig', [
            'trick' => $trick,
            'attachments' => $attachments,
            'comments' => $comments,
            'formComment' => $commentForm,
        ]);
    }

    #[Route(path: '/tricks', name: 'all_tricks')]
    public function showTricks(
        Request $request,
        TrickRepository $trickRepository
    ): Response {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $trickRepository->getTricksPaginator($offset);

        return $this->render('page/tricks.html.twig', [
            'tricks' => $paginator,
            'previous' => $offset - TrickRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + TrickRepository::PAGINATOR_PER_PAGE)
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/deleteTrick/{name}', name: 'delete_trick')]
    public function deleteTrick(string $name): Response
    {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);

        $this->em->remove($trick);
        $this->em->flush();

        $this->addFlash('success', 'La figure a été correctement supprimé');

        return $this->redirectToRoute('app_home');
    }
}
