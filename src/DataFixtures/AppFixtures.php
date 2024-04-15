<?php

namespace App\DataFixtures;

use DateTime;
use App\Entity\Trick;
use App\Service\DateService;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    public function load(
        ObjectManager $manager,
    ): void
    {
        $dateNow = new DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');
        
        for ($i = 0; $i < 20; $i++) {
            $trick = new Trick;
            $trick->setName('Trick ' . $i);
            $trick->setGroupTrick('Groupe ' . $i);
            $trick->setDescription('Description ' . $i);
            $trick->setDateCreate($dateNow);
        }
    }
}