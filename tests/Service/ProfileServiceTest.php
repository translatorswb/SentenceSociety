<?php

namespace App\Tests\Service;

use App\Entity\User;
use App\Model\TranslationSession;
use App\Service\ProfileService;
use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ProfileServiceTest extends KernelTestCase
{
    use JsonAssertions;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;


    /** @var ProfileService */
    private $profileService;

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
            ->get(ProfileService::class);
    }

    function testHighscores()
    {
        // no data available, get 0
        $this->assertCount(0, $this->profileService->getHighscores());

        // add users with highscores
        $this->profileService->createUserWithCode('Kia_007', 'pass123');
        $this->profileService->addUserScore('Kia_007', 10000);

        $this->profileService->createUserWithCode('MahletKL', 'pass123');
        $this->profileService->addUserScore('MahletKL', 200);

        $this->profileService->createUserWithCode('Areda42', 'pass123');
        $this->profileService->addUserScore('Areda42', 550);


        // now we should get three
        $highscoresA = $this->profileService->getHighscores();
        $this->assertCount(3, $highscoresA);

        $this->assertEquals(10100, $highscoresA[0]['points']);
        $this->assertEquals(650, $highscoresA[1]['points']);
        $this->assertEquals(300, $highscoresA[2]['points']);

        // add some more
        $this->profileService->createUserWithCode('Essu12', 'pass123');
        $this->profileService->addUserScore('Essu12', 5000);

        $this->profileService->createUserWithCode('Essu13', 'pass123');
        $this->profileService->addUserScore('Essu13', 800);

        $this->profileService->createUserWithCode('Essu14', 'pass123');
        $this->profileService->addUserScore('Essu14', 100);

        // and we should still get 3
        $highscoresB = $this->profileService->getHighscores();
        $this->assertCount(3, $highscoresB);

        $this->assertEquals(10100, $highscoresB[0]['points']);
        $this->assertEquals(5100, $highscoresB[1]['points']);
        $this->assertEquals(900, $highscoresB[2]['points']);

    }

    function testRankInHighscores() {

        // add users with highscores
        $this->profileService->createUserWithCode('Kia_007', 'pass123');
        $this->profileService->addUserScore('Kia_007', 10000);

        $this->profileService->createUserWithCode('MahletKL', 'pass123');
        $this->profileService->addUserScore('MahletKL', 200);

        $this->profileService->createUserWithCode('Areda42', 'pass123');
        $this->profileService->addUserScore('Areda42', 550);

        // one-based: rank 1 is the top!
        $this->assertEquals(1, $this->profileService->getRankInHighscores('Kia_007'));
        $this->assertEquals(3, $this->profileService->getRankInHighscores('MahletKL'));
        $this->assertEquals(2, $this->profileService->getRankInHighscores('Areda42'));
    }

    function testUsernameAvailable() {
        $this->profileService->createUserWithCode('testuser1', 'xV5f&');

        $this->assertEquals(false, $this->profileService->checkNameAvailable('testuser1'));
        $this->assertEquals(true, $this->profileService->checkNameAvailable('testuser2'));

    }

    function testSignupBonus() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        $profileData = $this->profileService->getProfileData($user);

        // sign up bonus
        $this->assertJsonValueEquals($profileData, 'profile.points', 100);

    }
}
