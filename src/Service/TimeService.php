<?php

namespace App\Service;


class TimeService implements ITimeService
{
    function currentDateTime(): \DateTimeInterface
    {
        return new \DateTime();
    }
}