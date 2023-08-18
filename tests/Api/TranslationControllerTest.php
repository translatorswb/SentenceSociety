<?php

namespace App\Tests\Api;

use App\Entity\SourcePhrase;
use App\Entity\SourcePhraseAssignment;
use App\Entity\TargetPhrase;
use App\Entity\User;
use App\Repository\SourcePhraseRepository;
use App\Repository\TargetPhraseRepository;
use App\Repository\UserRepository;
use App\Service\ProfileService;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TranslationControllerTest extends WebTestCase
{
    static $TEST_PHRASE_1 = "Hello world test phrase 1";
    static $TEST_PHRASE_2 = "Hello world test phrase 2";
    static $TEST_PHRASE_3 = "Hello world test phrase 3";
    static $TEST_PHRASE_4 = "Hello world test phrase 4";
    static $TEST_PHRASE_5 = "Hello world test phrase 5";
    static $TEST_PHRASE_6 = "Hello world test phrase 6";
    static $TEST_PHRASE_7 = "Hello world test phrase 7";
    static $TEST_PHRASE_8 = "Hello world test phrase 8";
    static $TEST_PHRASE_9 = "Hello world test phrase 9";
    static $TEST_PHRASE_10 = "Hello world test phrase 10";

    /** @var ObjectManager */
    private $doctrine;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var SourcePhraseRepository */
    private $sourcePhraseRepository;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var int */
    private $user1_ID;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        // gets the special container that allows fetching private services
        // https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;

        $this->doctrine = $kernel->getContainer()->get('doctrine');

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->sourcePhraseRepository = $container->get(SourcePhraseRepository::class);
        $this->targetPhraseRepository = $container->get(TargetPhraseRepository::class);
        $this->userRepository = $container->get(UserRepository::class);

        //
        // add test source phrases with translations
        //
        foreach ([self::$TEST_PHRASE_1, self::$TEST_PHRASE_2] as $sourcePhraseText) {
            $sourcePhrase = new SourcePhrase();
            $sourcePhrase->setText($sourcePhraseText);
            $this->entityManager->persist($sourcePhrase);

            $targetPhrase = new TargetPhrase();
            $targetPhrase->setText("TRANSLATED VERSION OF " . $sourcePhraseText);
            $targetPhrase->setTimestamp(new \DateTime());
            $targetPhrase->setSource($sourcePhrase);
            $this->entityManager->persist($targetPhrase);
        }

        //
        // source phrases without translation
        //
        foreach ([self::$TEST_PHRASE_3, self::$TEST_PHRASE_4, self::$TEST_PHRASE_5] as $sourcePhraseText) {
            $sourcePhrase = new SourcePhrase();
            $sourcePhrase->setText($sourcePhraseText);
            $this->entityManager->persist($sourcePhrase);
        }

        /** @var ProfileService $profileService */
        $profileService = $container->get(ProfileService::class);

        //
        // add 2 users
        //
        $user1 = $profileService->createUserWithCode('nick1', 'ABC123');
        $this->user1_ID = $user1->getId();
        $profileService->createUserWithCode('nick2', 'ABC456');

        //
        $this->entityManager->flush();
    }

    public function testGetChallenge()
    {
        $client = static::createClient();
        $client->request('POST', '/api/source/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('text', $responseData['data']);
        // 3 is the first one that doesn't have translations yet
        $this->assertEquals(self::$TEST_PHRASE_3, $responseData['data']['text'], "unexpected phrase returned");
    }

    public function testSkipNextChallenge()
    {
        // get
        $client = static::createClient();
        $client->request('POST', '/api/source/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('text', $responseData['data']);
        $this->assertEquals(self::$TEST_PHRASE_3, $responseData['data']['text'], "unexpected phrase returned");
        $firstResponseId = $responseData['data']['id'];

        // next!
        $client->request('POST', "/api/source/{$firstResponseId}/skip_next");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('text', $responseData['data']);
        $this->assertEquals(self::$TEST_PHRASE_4, $responseData['data']['text'], "unexpected phrase returned");
        $secondResponseId = $responseData['data']['id'];

        // next!
        $client->request('POST', "/api/source/{$secondResponseId}/skip_next");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('text', $responseData['data']);
        $this->assertEquals(self::$TEST_PHRASE_5, $responseData['data']['text'], "unexpected phrase returned");
        $thirdResponseId = $responseData['data']['id'];

        // next!
        $client->request('POST', "/api/source/{$thirdResponseId}/skip_next");
        $fourthResponseId = json_decode($client->getResponse()->getContent(), true)['data']['id'];

        // 2 more
        $client->request('POST', "/api/source/{$fourthResponseId}/skip_next");
        $fifthResponseId = json_decode($client->getResponse()->getContent(), true)['data']['id'];

        $client->request('POST', "/api/source/{$fifthResponseId}/skip_next");

        // now we are out of options and should get a 404
        $this->assertEquals(404, $client->getResponse()->getStatusCode());
    }

    public function testSubmitTranslation()
    {
        //
        // create the correct database content
        //

        /** @var SourcePhrase $sourcePhrase */
        $sourcePhrase = $this->doctrine
            ->getRepository(SourcePhrase::class)
            ->findOneBy(['phrase' => self::$TEST_PHRASE_1]);

//         ... something thats changes the DB state
//        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
//        die;
//         now the DB changes are actually persisted and you can debug them

        $this->assertNotNull($sourcePhrase);

        $sourcePhraseId = $sourcePhrase->getId();

        //
        // actual test
        //
        $client = static::createClient();
        $client->request(
            'POST',
            "/api/source/{$sourcePhraseId}/target",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"text":"Fabien"}'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('phrase', $responseData);
    }


    public function testVoteOnTranslation()
    {
        $client = static::createClient();

        // get translation
        $client->request('POST', '/api/target/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $targetId = $responseData['data']['id'];

        // vote
        $client->request(
            'POST',
            "/api/target/{$targetId}/rating",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"rating": 1}'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
//        $this->assertJson($response->getContent());
//        $responseData = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('phrase', $responseData);
    }

    /*
    public function testFlagSource()
    {
        $client = static::createClient();
        $client->request('POST', '/api/source/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $sourceId = $responseData['data']['id'];

        $client->request('POST', "/api/source/{$sourceId}/flag");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
//        $this->assertArrayHasKey('phrase', $responseData);
    }
    */

    public function testFlagTranslation()
    {
        $client = static::createClient();
        $client->request('POST', '/api/target/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $targetId = $responseData['data']['id'];

        $client->request('POST', "/api/target/{$targetId}/flag");
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
    }

    public function testOrderOfTranslations() {

        //
        // additional sentences
        //
        foreach ([
            self::$TEST_PHRASE_6, self::$TEST_PHRASE_7, self::$TEST_PHRASE_8, self::$TEST_PHRASE_9, self::$TEST_PHRASE_10
                 ] as $sourcePhraseText) {
            $sourcePhrase = new SourcePhrase();
            $sourcePhrase->setText($sourcePhraseText);
            $this->entityManager->persist($sourcePhrase);
        }
        $this->entityManager->flush();



        function getSourcePhrase(Client $client) {
            // get source (id)
            $client->request('POST', '/api/source/next');
//            $this->assertEquals(200, $client->getResponse()->getStatusCode());
            $response = $client->getResponse();
            $responseData = json_decode($response->getContent(), true);
            $sourceId = $responseData['data']['id'];
            return $sourceId;
        }

        function submitTranslation(Client $client, $sourceId, $translation) {
            $client->request(
                'POST',
                "/api/source/{$sourceId}/target",
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                '{"text": "' . $translation . '"}'
            );
            return $client->getResponse();
        }

        $user1Client = static::createClient();
        $user2Client = static::createClient();
        $user3Client = static::createClient();

        $client1_SourcePhrase1_id = getSourcePhrase($user1Client);
        $this->assertNotNull($client1_SourcePhrase1_id);

        $client1_SourcePhrase2_id = getSourcePhrase($user1Client);
        $this->assertNotNull($client1_SourcePhrase2_id);

        // same person getting source sentence twice -> same sentence
        $this->assertEquals($client1_SourcePhrase1_id, $client1_SourcePhrase2_id);

        // now client2 comes along...
        $client2_SourcePhrase1_id = getSourcePhrase($user2Client);
        $this->assertNotNull($client2_SourcePhrase1_id);

        // another person getting a source sentence -> same (as it has not been translated yet)
        $this->assertEquals($client1_SourcePhrase1_id, $client2_SourcePhrase1_id);



        // client 1 translates translation 1
        submitTranslation($user1Client, $client1_SourcePhrase1_id, "translation-1");

        // now client2 comes again
        $client2_SourcePhrase2_id = getSourcePhrase($user2Client);
        $this->assertNotNull($client2_SourcePhrase2_id);



        // This should not be a _new_ sentence! (as the 'first' sentence has been translated now)
        $this->assertNotEquals($client1_SourcePhrase1_id, $client2_SourcePhrase2_id);



//        $this->assertEquals(200, $client->getResponse()->getStatusCode());


    }

    function sharedAfterRoundBonus()
    {
        /** @var SourcePhrase $sourcePhrase */
        $sourcePhrase = $this->doctrine
            ->getRepository(SourcePhrase::class)
            ->findOneBy(['phrase' => self::$TEST_PHRASE_1]);

        $this->assertNotNull($sourcePhrase);
        $sourcePhraseId = $sourcePhrase->getId();

        //
        // translate
        //
        $client = static::createClient();
        $client->request(
            'POST',
            "/api/source/{$sourcePhraseId}/target",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"text":"Fabien"}'
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);

        //
        // validate
        //
        $client->request('POST', '/api/target/next');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $targetId = $responseData['data']['id'];

        // vote twice
        foreach ([1, 1] as $rating) {
            $client->request(
                'POST',
                "/api/target/{$targetId}/rating",
                [],
                [],
                ['CONTENT_TYPE' => 'application/json'],
                "{\"rating\": ${rating}}"
            );
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }

        //
        // request round bonus
        //
        $client->request(
            'POST',
            "/api/requestbonus",
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            null
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        return [$client];
    }
    function testLoginAfterRoundBonus() {
        [$client] = $this->sharedAfterRoundBonus();

        $userBefore = $this->userRepository->findOneBy(['name' => 'nick1']);
        $pointsBeforeLogin = $userBefore->getAwardedPoints()->toArray();
        $this->assertEquals(0, count($userBefore->getTargetPhrases()));
        //
        // login
        //
        $client->request(
            'POST',
            '/api/login',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick1',
                'code' => 'ABC123'
            ])
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //
        // check if links have been made
        //

        // clear to ensure we re-fetch the data
        $this->entityManager->clear();
        $userAfter = $this->userRepository->findOneBy(['name' => 'nick1']);
        $pointsAfterLogin = $userAfter->getAwardedPoints()->toArray();
        $targetPhrasesAfterLogin = $userAfter->getTargetPhrases();
        $ratingsAfterLogin = $userAfter->getRatings();

        // we expect 1 'awardedpoints' more after logging in
        $this->assertEquals(1, count($pointsAfterLogin) - count($pointsBeforeLogin));
        $this->assertEquals(1, count($targetPhrasesAfterLogin));
        $this->assertEquals(2, count($ratingsAfterLogin));
    }
}