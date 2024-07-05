<?php

namespace App\DataFixtures;

use App\Entity\Groups;
use DateTime;
use App\Entity\User;
use App\Entity\Trick;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private EntityManagerInterface $em
    ) {
    }

    public function load(
        ObjectManager $manager,
    ): void {
        $dateNow = new DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $user = new User();
        $user->setUsername('snowtrick');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setEmail('test@test.fr');
        $user->setIsVerified(true);
        $manager->persist($user);

        $groups = [
            [
                'name' => 'Les grabs',
                'illustration_url' => 'indy_grab.png',
            ],
            [
                'name' => 'Les rotations',
                'illustration_url' => '360.jpg',
            ],
            [
                'name' => 'Les flips',
                'illustration_url' => 'flip.jpg',
            ],
            [
                'name' => 'Les rotations désaxées',
                'illustration_url' => 'rotation_desax.jpg',
            ],
            [
                'name' => 'Les slides',
                'illustration_url' => 'slide.webp',
            ],
            [
                'name' => 'Les one foot tricks',
                'illustration_url' => 'onefoottrick.jpg',
            ],
        ];

        $tricks = [
            [
                'name' => 'Mute',
                'description' => 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant',
                'group' => 'Les grabs'
            ],
            [
                'name' => 'Melancholie',
                'description' => 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant',
                'group' => 'Les grabs'
            ],
            [
                'name' => 'Indy',
                'description' => 'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière',
                'group' => 'Les grabs'
            ],
            [
                'name' => '180',
                'description' => 'Désigne un demi-tour, soit 180 degrés d\'angle',
                'group' => 'Les rotations'
            ],
            [
                'name' => '360',
                'description' => 'Trois six pour un tour complet',
                'group' => 'Les rotations'
            ],
            [
                'name' => 'Front flip',
                'description' => 'Un flip est une rotation verticale, rotations en avant',
                'group' => 'Les flips'
            ],
            [
                'name' => 'Back flip',
                'description' => 'Un flip est une rotation verticale, rotations en arrière',
                'group' => 'Les flips'
            ],
            [
                'name' => 'Nose slide',
                'description' => 'On peut slider avec la planche centrée par rapport à la barre, mais aussi en nose slide, c\'est-à-dire l\'avant de la planche sur la barre',
                'group' => 'Les slides'
            ],
            [
                'name' => 'One foot tricks',
                'description' => 'Figures réalisée avec un pied décroché de la fixation, afin de tendre la jambe correspondante pour mettre en évidence le fait que le pied n\'est pas fixé',
                'group' => 'Les one foot tricks'
            ],
            [
                'name' => 'Tail slide',
                'description' => 'On peut slider avec la planche centrée par rapport à la barre, mais aussi en tail slide, c\'est-à-dire l\'arrière de la planche sur la barre',
                'group' => 'Les slides'
            ],

        ];

        foreach ($groups as $groupTrick) {
            $group = new Groups();
            $group->setName($groupTrick['name']);
            $group->setIllustrationUrl($groupTrick['illustration_url']);
            foreach ($tricks as $trick) {
                if ($group->getName() === $trick['group']) {
                    $addTrick = new Trick;
                    $addTrick->setName($trick['name']);
                    $addTrick->setDescription($trick['description']);
                    $addTrick->setDateCreate($dateNow);
                    $addTrick->setUser($user);
                    $addTrick->setGroups($group);
                    $manager->persist($addTrick);
                }
            }
            $manager->persist($group);
        }
        $manager->flush();
    }
}