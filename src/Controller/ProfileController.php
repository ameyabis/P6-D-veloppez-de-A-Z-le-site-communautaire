<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Picture;
use App\Form\ProfilePictureType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        public EntityManagerInterface $em,
    ) {
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/profile', name: 'profile')]
    public function showProfile(
        #[CurrentUser] ?User $user,
        Request $request,
        ParameterBagInterface $params,
    ): Response {
        $pictureProfile = $user->getProfilePicture();

        $form = $this->createForm(ProfilePictureType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $updatePicture = $request->files->all()['profile_picture']['profilePicture'];

            $extension = $updatePicture->guessExtension();
            $path = $params->get('images_directory');
            $fichier = $updatePicture->getFileName();

            $user->setProfilePicture($fichier . '.' . $extension);

            $this->em->persist($user);
            $updatePicture->move($path . '/', $fichier . '.' . $extension);
            $this->em->flush();

            return $this->redirectToRoute('profile');
        }

        return $this->render('page/profile.html.twig', [
            'user' => $user,
            'picture' => $pictureProfile,
            'form' => $form->createView(),
        ]);
    }
}
