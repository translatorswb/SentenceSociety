<?php

namespace App\Tests\Service;

use App\Entity\SourcePhrase;
use App\Entity\TargetPhrase;
use App\Entity\User;
use App\Model\TranslationSession;
use App\Repository\SourcePhraseRepository;
use App\Repository\TargetPhraseRepository;
use App\Service\ITimeService;
use App\Service\MockTimeService;
use App\Service\ProfileService;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Helmich\JsonAssert\JsonAssertions;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class TranslationsServiceTest extends KernelTestCase
{
    use JsonAssertions;

    static $TEST_PHRASE_1 = "Hello world test phrase 1";
    static $TEST_PHRASE_2 = "Hello world test phrase 2";
    static $TEST_PHRASE_3 = "Hello world test phrase 3";

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;


    /** @var ProfileService */
    private $profileService;

    /** @var TranslationService */
    private $translationService;

    /** @var SourcePhraseRepository */
    private $sourcePhraseRepository;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

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

        $this->translationService = $container
            ->get(TranslationService::class);


        $this->sourcePhraseRepository = $container->get(SourcePhraseRepository::class);
        $this->targetPhraseRepository = $container->get(TargetPhraseRepository::class);

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

            $targetPhrase2 = new TargetPhrase();
            $targetPhrase2->setText("TRANSLATED VERSION (2) OF " . $sourcePhraseText);
            $targetPhrase2->setTimestamp(new \DateTime());
            $targetPhrase2->setSource($sourcePhrase);
            $this->entityManager->persist($targetPhrase2);

            $targetPhrase3 = new TargetPhrase();
            $targetPhrase3->setText("TRANSLATED VERSION (3) OF " . $sourcePhraseText);
            $targetPhrase3->setTimestamp(new \DateTime());
            $targetPhrase3->setSource($sourcePhrase);
            $this->entityManager->persist($targetPhrase3);
        }

        //
        // source phrase without translation
        //
        $sourcePhrase = new SourcePhrase();
        $sourcePhrase->setText(self::$TEST_PHRASE_3);
        $this->entityManager->persist($sourcePhrase);

        //
        // add 2 users
        //
        $user1 = new User();
        $user1->setPassword('ABC123');
        $user1->setName('nick1');
        $user1->setCreated(new \DateTime());
        $this->entityManager->persist($user1);

        $user2 = new User();
        $user2->setPassword('ABC456');
        $user2->setName('nick2');
        $user2->setCreated(new \DateTime());
        $this->entityManager->persist($user2);

        //

        $this->entityManager->flush();
    }

    function testNextTranslationNotLoggedIn() {

        //
        // not logged in session
        //
        $translationSession = new TranslationSession();
        $source = $this->translationService->getNextSource($translationSession);
        $this->assertEquals($source->getLevel(), 1);
    }

    function testNextTranslationLevel1() {

        $translationSession = new TranslationSession();
        $source = $this->translationService->getNextSource($translationSession);
        $this->assertEquals($source->getLevel(), 1);
    }

    function testNextTranslationLevel2() {

        //
        // make sure there is a level 2 sentence
        //

        $sourcePhrase = new SourcePhrase();
        $sourcePhrase->setText('level 2 phrase');
        $sourcePhrase->setLevel(2);
        $this->entityManager->persist($sourcePhrase);
        $this->entityManager->flush();

        $translationSession = new TranslationSession();
        $translationSession->setUserLevel(2);

        $source = $this->translationService->getNextSource($translationSession);
        $this->assertEquals($source->getLevel(), 2);
    }

    function testNextTranslationLevel3() {

        $sourcePhrase = new SourcePhrase();
        $sourcePhrase->setText('level 2 phrase');
        $sourcePhrase->setLevel(2);
        $this->entityManager->persist($sourcePhrase);

        $sourcePhrase = new SourcePhrase();
        $sourcePhrase->setText('level 3 phrase');
        $sourcePhrase->setLevel(3);
        $this->entityManager->persist($sourcePhrase);
        $this->entityManager->flush();

        $translationSession = new TranslationSession();
        $translationSession->setUserLevel(3);
        $source = $this->translationService->getNextSource($translationSession);
        $this->assertEquals($source->getLevel(), 3);

        // first prefer simple version
        $source = $this->translationService->skipAndNextSource($translationSession, $source->getId());
        $this->assertEquals($source->getLevel(), 1);

        // then 'harder' version
        $source = $this->translationService->skipAndNextSource($translationSession, $source->getId());
        $this->assertEquals($source->getLevel(), 2);
    }

    function testNextValidation() {

        $translationSession = new TranslationSession();

        $target = $this->translationService->getNextTarget($translationSession);
        $nextTarget = $this->translationService->validateTarget($translationSession, $target->getId(), 1);
        $this->assertNotEquals($target->getId(), $nextTarget->getId());
    }

    function testBonusNotLoggedIn() {

        $translationSession = new TranslationSession();

        $source = $this->translationService->getNextSource($translationSession);
        $this->translationService->addTranslationForSource($translationSession, $source->getId(), 'translation1');

        $target = $this->translationService->getNextTarget($translationSession);
        $nextTarget = $this->translationService->validateTarget($translationSession, $target->getId(), 1);
        $this->assertNotEquals($target->getId(), $nextTarget->getId());

        $this->translationService->validateTarget($translationSession, $nextTarget->getId(), 2);

        // now we did 1 translation and 2 validations -> request bonus!
        $this->profileService->requestBonus($translationSession);

        $this->assertCount(1,  $translationSession->postponedBonuses());
    }

    function testBonusLoggedIn() {

        $translationSession = new TranslationSession();

        $user = $this->profileService->createUserWithCode('user1', 'pass1');

        $this->assertCount(1, $user->getAwardedPoints());

        // 'log in' the user
        $translationSession->setUserId($user->getId());

        $source = $this->translationService->getNextSource($translationSession);
        $this->translationService->addTranslationForSource($translationSession, $source->getId(), 'translation1');

        $target = $this->translationService->getNextTarget($translationSession);
        $nextTarget = $this->translationService->validateTarget($translationSession, $target->getId(), 1);
        $this->assertNotEquals($target->getId(), $nextTarget->getId());

        $this->translationService->validateTarget($translationSession, $nextTarget->getId(), 2);

        // now we did 1 translation and 2 validations -> request bonus!
        $this->profileService->requestBonus($translationSession);

        $this->assertCount(0,  $translationSession->postponedBonuses());

        $awardedPoints = $user->getAwardedPoints();
        $this->assertCount(2, $awardedPoints);
    }

    function testProfileVoteCounts() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        $profileData = $this->profileService->getProfileData($user);

        // just signed up -> no votes
        $this->assertJsonValueEquals($profileData, 'profile.votes.down', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up1', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up2', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up3', 0);

        // no pending sentences
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 0);

        // no reviewed sentences
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 0);

        // submit one translation
        $translationSession = new TranslationSession();
        $translationSession->setUserId($user->getId());
        $translationSource = $this->translationService->getNextSource($translationSession);
        $translation = $this->translationService->addTranslationForSource($translationSession, $translationSource->getId(), 'test translation');

//        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
//        die;

        $profileData = $this->profileService->getProfileData($user);
        // should give one pending translation
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 1);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 0);

        // submit another translation
        $translationSession = new TranslationSession();
        $translationSession->setUserId($user->getId());
        $translationSource = $this->translationService->getNextSource($translationSession);
        $translation2 = $this->translationService->addTranslationForSource($translationSession, $translationSource->getId(), 'test translation 2');

        $profileData = $this->profileService->getProfileData($user);
        // should give 2 pending translations
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 2);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 0);

        // add single review
        $reviewingUser1 = $this->profileService->createUserWithCode('reviewuser1', 'xV5f&');
        $reviewSession = new TranslationSession();
        $reviewSession->setUserId($reviewingUser1->getId());
        $this->translationService->validateTarget($reviewSession, $translation->getId(), 2);

        $profileData = $this->profileService->getProfileData($user);
        // should give 2 pending translations
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 2);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 0);

        // add another positive review
        $reviewingUser2 = $this->profileService->createUserWithCode('reviewuser2', 'xV5f&');
        $reviewSession2 = new TranslationSession();
        $reviewSession2->setUserId($reviewingUser2->getId());
        $this->translationService->validateTarget($reviewSession2, $translation->getId(), 2);

//        \DAMA\DoctrineTestBundle\Doctrine\DBAL\StaticDriver::commit();
//        die;

        $profileData = $this->profileService->getProfileData($user);
        // should give 1 of each
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 1);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 1);
        // '+1' and '+2' votes
        $this->assertJsonValueEquals($profileData, 'profile.votes.down', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up1', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up2', 1);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up3', 0);

        //
        // now add 2 conflicting votes to the other translation
        //
        $reviewSession->setUserId($reviewingUser1->getId());
        $this->translationService->validateTarget($reviewSession, $translation2->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $translation2->getId(), -1);


        $profileData = $this->profileService->getProfileData($user);

        // should give 1 of each
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 1);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 1);
        // '+1' and '+2' votes

        $this->assertJsonValueEquals($profileData, 'profile.votes.down', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up1', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up2', 1);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up3', 0);

        //
        // now add 2 additional votes to the other translation to finally resolve it
        //
        $reviewSession->setUserId($reviewingUser1->getId());
        $this->translationService->validateTarget($reviewSession, $translation2->getId(), 2);
        $this->translationService->validateTarget($reviewSession, $translation2->getId(), 3);

        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.translationsPending', 0);
        $this->assertJsonValueEquals($profileData, 'profile.translationsReviewed', 2);
        $this->assertJsonValueEquals($profileData, 'profile.votes.down', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up1', 0);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up2', 2);
        $this->assertJsonValueEquals($profileData, 'profile.votes.up3', 0);
    }


    function addTranslationWithValidations(EntityManagerInterface $entityManager, $translationService, $userId, $reviews) {
        // insert translation, and validation
        $sourceCandidate = new SourcePhrase();
        $sourceCandidate->setText('test');
        $entityManager->persist($sourceCandidate);
        $entityManager->flush();
        $translationSession = new TranslationSession();
        $translationSession->setUserId($userId);
        $source = $translationService->getNextSource($translationSession);
        $target = $translationService->addTranslationForSource($translationSession, $source->getId(), 'translation1');

        $reviewSession = new TranslationSession();
        foreach ($reviews as $review) {
            $translationService->validateTarget($reviewSession, $target->getId(), $review);
        }
    }

    function testUpvoteCountToRanks() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');

        // start out at rank 0
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 0);

        // add one positive translation
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);

        // still rank 0
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 0);

        // add 4 more
        for ($i = 0; $i < 4; $i += 1) {
            $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);
        }

        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 1);
        // rank 0 (newby) .. 11 (wizard)

        // add 2 downvoted ones
        for ($i = 0; $i < 2; $i += 1) {
            $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [-1, -1]);
        }

        // now we are one rank down to 0
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 0);

        // add 10 more (10 * 3 <=> 2 * -15)
        for ($i = 0; $i < 10; $i += 1) {
            $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);
        }

        // back at rank 1
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 1);
    }

    function testUserLevel() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');

        // start out at rank 0
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.rank', 0);

        // rank 0..3 = level 1
        // rank 4..7 = level 2
        // rank 8..11 = level 3
        $level1 = ProfileService::$RANK_BREAKPOINTS[0];
        $level2 = ProfileService::$RANK_BREAKPOINTS[4];
        $level3 = ProfileService::$RANK_BREAKPOINTS[8];

        // insert translation, and validation
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);

        // still rank 0
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.level', 1);

        $reviewSession = new TranslationSession();

        // add lots of +3's
        $requiredPlus3 = ($level2 / 3) + 1;
        for ($i = 0; $i < $requiredPlus3; $i += 1) {
            $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);
        }

        // then we should now be at level 2
        $profileData = $this->profileService->getProfileData($user);
        $this->assertJsonValueEquals($profileData, 'profile.level', 2);
    }


    function testConsecutiveDaysBonus() {

        function doRound(TranslationSession $translationSession, TranslationService $translationService, ProfileService $profileService) {
            $translationSource = $translationService->getNextSource($translationSession);
            $translation = $translationService->addTranslationForSource($translationSession, $translationSource->getId(), 'test translation 288');
            $validation1 = $translationService->getNextTarget($translationSession);
            $validation2 = $translationService->validateTarget($translationSession, $validation1->getId(), 1);
            $translationService->validateTarget($translationSession, $validation2->getId(), 1);
            $profileService->requestBonus($translationSession);
        }

        $container = self::$container;

        $timeService = $container
            ->get(ITimeService::class) ;

        if ($timeService instanceof MockTimeService) {

            $timeService->setMockDateTime(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-01-01 09:30:00'));

            $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
            $translationSession = new TranslationSession();
            $translationSession->setUserId($user->getId());
            $profileData = $this->profileService->getProfileData($user);
            // sign up bonus
            $this->assertJsonValueEquals($profileData, 'profile.points', 100);

            // set datetime
            $timeService->setMockDateTime(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-01-02 09:30:00'));
            doRound($translationSession, $this->translationService, $this->profileService);

            $profileData = $this->profileService->getProfileData($user);
            // sign up bonus
            $this->assertJsonValueEquals($profileData, 'profile.points', 100 + 15);

            // skip to next day
            $timeService->setMockDateTime(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-01-03 09:30:00'));

            // bonus awarded for finishing a round on two consecutive days

            doRound($translationSession, $this->translationService, $this->profileService);

            $profileData = $this->profileService->getProfileData($user);
            $this->assertJsonValueEquals($profileData, 'profile.points', 100 + 15 + 100 + 15);

            // 8 hours later
            $timeService->setMockDateTime(\DateTime::createFromFormat('Y-m-d H:i:s', '2018-01-03 17:30:00'));

            // no bonus awarded for finishing a round on two consecutive days
            doRound($translationSession, $this->translationService, $this->profileService);
            $profileData = $this->profileService->getProfileData($user);
            $this->assertJsonValueEquals($profileData, 'profile.points', 100 + 15 + 100 + 15 + 15);

        } else {
            $this->fail("MockTimeService missing - was the custom testing kernel loaded?");
        }



    }

    function sharedValidationSetup() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        // insert translation, and validation
        $sourceCandidate = new SourcePhrase();
        $sourceCandidate->setText('test');
        $this->entityManager->persist($sourceCandidate);
        $this->entityManager->flush();
        $translationSession = new TranslationSession();
        $translationSession->setUserId($user->getId());
        $source = $this->translationService->getNextSource($translationSession);
        $target = $this->translationService->addTranslationForSource($translationSession, $source->getId(), 'translation1');
        $reviewSession = new TranslationSession();
        return [
            $target,
            $reviewSession,
        ];
    }
    function testValidationLogic_OpenWithNoValidations() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Open1() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);
        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Open2() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Open2b() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 2);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Open2c() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Open3() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_OPEN, $target->getValidationState());
    }
    function testValidationLogic_Valid1() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(3, $target->getValidationScore());
    }
    function testValidationLogic_Valid2() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 2);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(2, $target->getValidationScore());
    }
    function testValidationLogic_Valid3() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 2);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 2);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(2, $target->getValidationScore());
    }
    function testValidationLogic_Valid4() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(1, $target->getValidationScore());
    }
    function testValidationLogic_Valid5() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(2, $target->getValidationScore());
    }
    function testValidationLogic_Valid6() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 3);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_POSITIVE, $target->getValidationState());
        $this->assertEquals(1, $target->getValidationScore());
    }
    function testValidationLogic_Invalid1() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_NEGATIVE, $target->getValidationState());
        $this->assertEquals(-1, $target->getValidationScore());

    }
    function testValidationLogic_Invalid2() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 2);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_NEGATIVE, $target->getValidationState());
        $this->assertEquals(-1, $target->getValidationScore());
    }
    function testValidationLogic_Inconclusive() {
        [
            $target,
            $reviewSession
        ] = $this->sharedValidationSetup();

        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), -1);
        $this->translationService->validateTarget($reviewSession, $target->getId(), 1);

        $this->assertEquals(TargetPhrase::VALIDATION_STATE_INCONCLUSIVE, $target->getValidationState());
        $this->assertEquals(0, $target->getValidationScore());

    }
}
