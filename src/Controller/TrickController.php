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
        private EntityManagerInterface $em,
        private FormService $formService,
    ) {
    }

    //Fonction pour créer et modifier notre trick
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/formTrick', name: 'form_trick', methods: ['GET', 'POST'])]
    public function createFormTrick(
        Request $request,
        TrickRepository $trickRepository,
        #[CurrentUser] ?User $user
    ): Response {
        $pictures = [];

        $formService = $this->formService->formDataTrick(
            $user,
            $request,
            $pictures
        );

        if ($formService['form']->isSubmitted()) {
            // if ($this->em->getRepository(Trick::class)->findOneBy(['name' => $completedForm['name']]) && $trick->getId() === null) {
            //     $this->addFlash('warning', 'Le nom de figure est déjà utilisé.');

            //     return $this->render('forms/formTrick.html.twig', [
            //         'formTrick' => $formTrick,
            //         'groups' => $groups
            //     ]);
            // }
            return $this->redirectToRoute('one_trick', ['name' => $request->request->all()['create_trick']['name']]);
        }

        return $this->render('forms/formTrick.html.twig', [
            'formTrick' => $formService['form'],
            'pictures' => $pictures
        ]);
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/formTrick/{name}', name: 'form_edit', methods: ['GET', 'POST'])]
    public function editFormTrick(
        string $name,
        Request $request,
        #[CurrentUser] ?User $user
    ): Response {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);
        $pictures = $this->em->getRepository(Picture::class)->findBy(['trick' => $trick->getId()]);
        // dd($trick);
        $formService = $this->formService->formDataTrick(
            $user,
            $request,
            $pictures,
            $trick,
        );

        if ($formService->isSubmitted()) {
            return $this->redirectToRoute('one_trick', ['name' => $request->request->all()['create_trick']['name']]);
        }

        return $this->render('forms/formTrick.html.twig', [
            'formTrick' => $formService,
            'pictures' => $pictures
        ]);
    }

    #[Route(path: '/trick/{name}', name: 'one_trick', methods: 'GET')]
    public function showOneTrick(
        string $name,
        Request $request,
        CommentController $commentController,
        DateService $dateService,
        #[CurrentUser] ?User $user,
    ): Response {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);
        $videos = $this->em->getRepository(Video::class)->findBy(['trick' => $trick->getId()]);

        $pictures = $this->em->getRepository(Picture::class)->findBy(['trick' => $trick->getId()]);
        $mainPicture = 'image/' . $trick->getGroups()->getIllustrationUrl();

        if (count($pictures) > 0) {
            $mainPicture = 'assets/uploads/' . $pictures[0]->getUrl();
        }

        $commentForm = $commentController->addComment(
            $request,
            $user,
            $trick
        );

        $comments = $commentController->getComment($trick->getId());

        return $this->render('page/trick.html.twig', [
            'trick' => $trick,
            'videos' => $videos,
            'pictures' => $pictures,
            'comments' => $comments,
            'formComment' => $commentForm,
            'mainPicture' => $mainPicture
        ]);
    }

    #[Route(path: '/tricks', name: 'all_tricks', methods: 'GET')]
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
    #[Route(path: '/delete-trick/{name}', name: 'delete_trick', methods: 'GET')]
    public function deleteTrick(string $name): Response
    {
        $trick = $this->em->getRepository(Trick::class)->findOneBy(['name' => $name]);

        $this->em->remove($trick);
        $this->em->flush();

        $this->addFlash('success', 'La figure a été correctement supprimé');

        return $this->redirectToRoute('app_home');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/delete-picture-from-trick/{url}', name: 'delete_one_picture', methods: 'GET')]
    public function deletePictureFromTrick(
        string $url,
        #[CurrentUser] ?User $user
    ): Response {
        $picture = $this->em->getRepository(Picture::class)->findOneBy(['url' => $url]);
        $trickName = $picture->getTrick()->getName();
        $this->em->remove($picture);
        $this->em->flush();

        unlink($this->getParameter('kernel.project_dir') . '/public/assets/uploads/' . $url);

        $this->addFlash('success', 'La photo a été supprimé avec succes.');

        return $this->redirectToRoute('form_edit', ['name' => $trickName]);
    }
}
