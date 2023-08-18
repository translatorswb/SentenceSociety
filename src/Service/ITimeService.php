<?php

namespace App\Service;


interface ITimeService
{
    function currentDateTime():\DateTimeInterface;
}