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
    #[Route(path: '/profile', name: 'profile', methods: ['GET', 'POST'])]
    public function showProfile(
        #[CurrentUser] ?User $user,
        Request $request,
        ParameterBagInterface $params,
    ): Response {
        $pictureProfile = $user->getProfilePicture();

        $form = $this->createForm(ProfilePictureType::class);
        $form->handleRequest($request);

        //Traitement des données du formulaire pour modifier l'image de profile
        if ($form->isSubmitted() && $form->isValid()) {
            $updatePicture = $request->files->all()['profile_picture']['profilePicture'];

            //On recupère l'extension du fichier
            $extension = $updatePicture->guessExtension();
            $path = $params->get('images_directory');
            $fichier = $updatePicture->getFileName();

            //Nous donnons un autre nom a l'image
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
