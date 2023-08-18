<?php

namespace App\Service;

class MockTimeService implements ITimeService
{
    private $mockDateTime;

    function setMockDateTime(\DateTimeInterface $dt) {
        $this->mockDateTime = $dt;
    }

    function currentDateTime(): \DateTimeInterface
    {
        if ($this->mockDateTime) {
            return $this->mockDateTime;
        } else {
            return new \DateTime();
        }
//        return new \DateTime("2018-02-06 16:41:58.955376");
    }

}