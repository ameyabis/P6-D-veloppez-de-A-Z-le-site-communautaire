<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Picture;
use App\Form\CreateTrickType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class FormService
{
    public function __construct(
        private EntityManagerInterface $em,
        private DateService $dateService,
        private FormFactoryInterface $form,
        private ParameterBagInterface $params
    ) {
    }

    public function formDataTrick(
        Trick $trick,
        User $user,
        Request $request
    ): ?FormInterface {
        $formTrick = $this->form->create(CreateTrickType::class, $trick);
        $formTrick->handleRequest($request);

        if ($formTrick->isSubmitted() && $formTrick->isValid()) {
            $completedForm = $request->request->all()['create_trick'];
            $pictures = $request->files->all()['create_trick']['pictures'];
            $dateNow = $this->dateService->dateNow();

            $trick->setName($completedForm['name']);
            $trick->setGroupTrick($completedForm['groupTrick']);
            $trick->setDescription($completedForm['description']);
            if ($trick->getId() === null) {
                $trick->setUser($user);
                $trick->setDateCreate($dateNow);
            } else {
                $trick->setDateEdit($dateNow);
            }

            $this->em->persist($trick);

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
        }

        return $formTrick;
    }
}
