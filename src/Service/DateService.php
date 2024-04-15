<?php

namespace App\Service;

use DateTime;
class DateService
{
    public function dateNow(): DateTime
    {
        $dateNow = new DateTime();
        $dateNow->setTimezone(new \DateTimeZone('Europe/Paris'));
        $dateNow->format('Y-m-d H:i:s');

        return $dateNow;
    }
}
