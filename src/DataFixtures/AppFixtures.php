<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\User;
use App\Entity\Trick;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher){}
    public function load(
        ObjectManager $manager,
    ): void
    {
        $dateNow = new DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        $user = new User();
        $user->setUsername('snowtrick');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
        $user->setEmail('test@test.fr');
        $user->setIsVerified(true);
        $manager->persist($user);
        
        for ($i = 0; $i < 20; $i++) {
            $trick = new Trick;
            $trick->setName('Trick ' . $i);
            $trick->setGroupTrick('Groupe ' . $i);
            $trick->setDescription('Description ' . $i);
            $trick->setDateCreate($dateNow);
            $trick->setUser($user);
            $manager->persist($trick);
        }
        $manager->flush();
    }
}