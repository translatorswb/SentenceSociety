<?php

namespace App\Tests\Service;


use App\Service\ITimeService;
use App\Service\MockTimeService;
use App\Service\ProfileService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TimeRelatedTest extends KernelTestCase
{
    //
    // This override doesn't work if other classes that extend KernelTestCase don't do this. (first one wins?)
    // https://github.com/symfony/symfony/issues/22661#issuecomment-299800727
    // using <server name="KERNEL_CLASS" value="App\Tests\TimingTestsKernel" /> in phpunit.xml.dist instead
    //
//    protected static function getKernelClass()
//    {
//        return 'App\Tests\TimingTestsKernel';
//    }

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        // gets the special container that allows fetching private services
        // see https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;

        $this->profileService = $container
            ->get(ITimeService::class);
    }

    function testHelloWorld() {
        $container = self::$container;

        $timeService = $container
            ->get(ITimeService::class) ;

        if ($timeService instanceof MockTimeService) {

            $timeService->setMockDateTime(new \DateTime("2018-02-03 16:41:58.955376"));
            var_dump($timeService->currentDateTime());

            $this->assertTrue(true);

        } else {
            $this->fail("MockTimeService missing - was the custom testing kernel loaded?");
        }


    }
}