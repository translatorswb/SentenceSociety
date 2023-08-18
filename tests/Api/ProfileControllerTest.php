<?php

namespace App\Tests\Api;

use App\Entity\SourcePhrase;
use App\Entity\User;
use App\Service\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    use JsonAssertions;

    static $USER1 = "nick1";
    static $USER2 = "nick2";
    static $PASSWORD = "ABC123";

    /** @var EntityManagerInterface */
    private $entityManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        // 'normal' container
        // $container = $kernel->getContainer();

        // gets the special container that allows fetching private services
        // https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
        $container = self::$container;

        /** @var ProfileService $profileService */
        $profileService = $container->get(ProfileService::class);

        $this->entityManager = $container
            ->get('doctrine')
            ->getManager();

        //
        // add 2 users
        //
        $profileService->createUserWithCode('nick1', self::$PASSWORD);
        $profileService->addUserScore('nick1', 10);
        $profileService->setUserRank('nick1', 3);

        $profileService->createUserWithCode('nick2', 'ABC123');
        $profileService->addUserScore('nick2', 20);

    }

    public function testLoginValidUser()
    {
        $client = static::createClient();
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
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
    }

    public function testLoginInValidUser()
    {
        $client = static::createClient();
//        $client->request('POST', '/api/login', [
//            'name' => 'nick1invalid',
//            'code' => 'ABC123'
//        ]);
        $client->request(
            'POST',
            '/api/login',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick1invalid',
                'code' => 'ABC123'
            ])
        );
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
    }

    public function testCheckCreateUser()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/checkname',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick3'
            ])
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('available', $responseData['data']);
        $this->assertEquals(true, $responseData['data']['available']);
    }

    public function testCheckCreateUserExisting()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/checkname',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick2'
            ])
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('available', $responseData['data']);
        $this->assertEquals(false, $responseData['data']['available']);
    }


    public function testCreateUser()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick3',
                'code' => 'mySecretCode42'
            ])
        );
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('profile', $responseData['data']);

        // https://github.com/martin-helmich/phpunit-json-assert
        $this->assertJsonValueEquals($responseData, 'data.name', 'nick3');
    }

    public function testCreateUserWithExistingName()
    {
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/register',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            json_encode([
                'name' => 'nick1',
                'code' => 'mySecretCode42'
            ])
        );

//        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
//        die;

        $this->assertEquals(409, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());

        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testGetProfileNotLoggedIn()
    {
        $client = static::createClient();

        $client->request('GET', '/api/profile');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        // assert there is no user data
        $this->assertArrayNotHasKey('user', $responseData);
        $this->assertArrayHasKey('errors', $responseData);

    }

    public function testGetProfileLoggedIn()
    {
        $client = static::createClient();
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
        $client->request('GET', '/api/profile');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        // assert there is  user data
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('name', $responseData['data']);
        $this->assertArrayHasKey('profile', $responseData['data']);
        $this->assertArrayHasKey('highscores', $responseData['data']);
        $this->assertArrayHasKey('scorehistory', $responseData['data']);

    }

    public function testLoginAndGetData()
    {
        $client = static::createClient();
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
        $response = $client->getResponse();
        $this->assertTrue($response->headers->contains('Content-Type', 'application/json'));
        $this->assertJson($response->getContent());
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);

        $client->request('GET', '/api/profile');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('name', $responseData['data']);
        $this->assertArrayHasKey('profile', $responseData['data']);
        $this->assertArrayHasKey('rank', $responseData['data']['profile']);
        $this->assertEquals(0, $responseData['data']['profile']['rank']);
        $this->assertArrayHasKey('highscores', $responseData['data']);
        $this->assertArrayHasKey('highscorePosition', $responseData['data']['profile']);
        $this->assertEquals(2, $responseData['data']['profile']['highscorePosition']);
        $highscores = $responseData['data'];

        // remove all cookies - the session should be disconnected
        $client->getCookieJar()->clear();

        $client->request('GET', '/api/profile');
        $this->assertEquals(403, $client->getResponse()->getStatusCode());
        $response = $client->getResponse();
        $responseData = json_decode($response->getContent(), true);
        // assert there is no user data
        $this->assertArrayNotHasKey('user', $responseData);
        $this->assertArrayHasKey('errors', $responseData);

    }
}