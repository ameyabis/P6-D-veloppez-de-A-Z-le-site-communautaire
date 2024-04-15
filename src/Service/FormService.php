<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Trick;
use App\Entity\Picture;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class FormService
{
    public function __construct(
        private EntityManagerInterface $em
    ){}

    public function FormDataTrick(
        Trick $trick,
        ParameterBagInterface $params,
        User $user,
        array $completedForm,
        array $pictures
    ): Trick {
        $dateNow = new \DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');


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
            $path = $params->get('images_directory');
            $fichier = $pictureUpload->getFileName();

            $picture = new Picture();
            $picture->setUrl($fichier . '.' . $extension);
            $picture->setTrick($trick);

            $this->em->persist($picture);
            $pictureUpload->move($path . '/', $fichier . '.' . $extension);
        }

        $this->em->flush();

        return $trick;
    }
}