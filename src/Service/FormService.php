<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Groups;
use App\Entity\Picture;
use App\Form\CreateTrickType;
use App\Repository\TrickRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class FormService extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private DateService $dateService,
        private FormFactoryInterface $form,
        private ParameterBagInterface $params,
        private TrickRepository $trickRepository,
    ) {
    }

    public function formDataTrick(
        Trick $trick,
        User $user,
        Request $request,
        ?array $picturesPath
    ): ?Response {
        $originalVideos = new ArrayCollection();

        foreach ($trick->getVideos() as $video) {
            $originalVideos->add($video);
        }

        $formTrick = $this->form->create(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);
        $groups = $this->em->getRepository(Groups::class)->findAll();

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()['create_trick'];
            $pictures = $request->files->all()['create_trick']['pictures'];
            $dateNow = $this->dateService->dateNow();

            foreach ($originalVideos as $tag) {
                if (false === $trick->getVideos()->contains($tag)) {
                    $this->em->remove($tag);;

                    // $this->em->persist($tag);
                }
            }
            // $this->em->persist($trick);

            if ($this->em->getRepository(Trick::class)->findOneBy(['name' => $completedForm['name']]) && $trick->getId() === null) {
                $this->addFlash('warning', 'Le nom de figure est déjà utilisé.');

                return $this->render('forms/formTrick.html.twig', [
                    'formTrick' => $formTrick,
                    'groups' => $groups
                ]);
            }

            foreach ($groups as $group) {
                if ((int) $completedForm['groups'] === $group->getId()) {
                    $trick->setName($completedForm['name']);
                    $trick->setGroups($group);
                    $trick->setDescription($completedForm['description']);
                    if ($trick->getId() === null) {
                        $trick->setUser($user);
                        $trick->setDateCreate($dateNow);
                    } else {
                        $trick->setDateEdit($dateNow);
                    }

                    $this->em->persist($trick);
                }
            }

            //Gestion des videos
            foreach ($trick->getVideos() as $video) {
                $video->setTrick($trick);

                $this->em->persist($video);
            }

            //Gestion des images
            foreach ($pictures as $pictureUpload) {
                $extension = $pictureUpload->guessExtension();
                $path = $this->params->get('images_directory');
                $fichier = $pictureUpload->getFileName();

                $picture = new Picture();
                $picture->setUrl($fichier . '.' . $extension);
                $picture->setTrick($trick);

                $this->em->persist($picture);
                $pictureUpload->move($path . '/', $fichier . '.' . $extension);
            }

            $this->em->flush();

            $this->addFlash('success', 'La figure a bien été enregistré.');

            $offset = max(0, $request->query->getInt('offset', 0));
            $paginator = $this->trickRepository->getTricksPaginator($offset);

            return $this->render('home/homePage.html.twig', [
                'tricks' => $paginator,
                'previous' => $offset - TrickRepository::PAGINATOR_PER_PAGE,
                'next' => min(count($paginator), $offset + TrickRepository::PAGINATOR_PER_PAGE)
            ]);
        } else {
            return $this->render('forms/formTrick.html.twig', [
                'formTrick' => $formTrick,
                'groups' => $groups,
                'pictures' => $picturesPath,
            ]);
        }
    }
}
