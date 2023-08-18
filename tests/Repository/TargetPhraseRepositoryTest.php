<?php

namespace App\Tests\Repository;

use App\Entity\SourcePhrase;
use App\Entity\TargetPhrase;
use App\Model\TranslationSession;
use App\Repository\TargetPhraseRepository;
use App\Service\ProfileService;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TargetPhraseRepositoryTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var TranslationService */
    private $translationService;

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

        $this->targetPhraseRepository = $container
            ->get(TargetPhraseRepository::class);

        $this->translationService = $container
            ->get(TranslationService::class);

        $this->profileService = $container
            ->get(ProfileService::class);
    }

    function addTranslationWithValidations(EntityManagerInterface $entityManager, TranslationService $translationService, $userId, $reviews) {
        // insert translation, and validation
        $sourceCandidate = new SourcePhrase();
        $sourceCandidate->setText('test');
        $entityManager->persist($sourceCandidate);
        $entityManager->flush();
        $translationSession = new TranslationSession();
        if ($userId) {
            $translationSession->setUserId($userId);
        }
        $source = $translationService->getNextSource($translationSession);
        $target = $translationService->addTranslationForSource($translationSession, $source->getId(), 'translation1');

        $reviewSession = new TranslationSession();
        foreach ($reviews as $review) {
            $translationService->validateTarget($reviewSession, $target->getId(), $review);
        }
    }

    function testTotalCount() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        $this->assertEquals(0, $this->targetPhraseRepository->totalNumberOfTargetPhrases());
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);
        $this->assertEquals(1, $this->targetPhraseRepository->totalNumberOfTargetPhrases());
    }

    function testValidatedSinceCount() {
        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        $yesterday = new \DateTime();
        $yesterday->add(\DateInterval::createFromDateString('yesterday')); // 'add' a negative interval to get yesterday
        $counts = $this->targetPhraseRepository->validatePhrasesSince($yesterday);
//        print_r($counts);
        $this->assertEquals(0, $counts['up1']);
        $this->assertEquals(0, $counts['up2']);
        $this->assertEquals(0, $counts['up3']);

        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [3, 3]);
        $counts = $this->targetPhraseRepository->validatePhrasesSince($yesterday);
        $this->assertEquals(0, $counts['up1']);
        $this->assertEquals(0, $counts['up2']);
        $this->assertEquals(1, $counts['up3']);

        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [-1, -1]);
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [1, 1]);
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [2, 2]);
        $counts = $this->targetPhraseRepository->validatePhrasesSince($yesterday);
        $this->assertEquals(1, $counts['up1']);
        $this->assertEquals(1, $counts['up2']);
        $this->assertEquals(1, $counts['up3']);

        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [2, 2]);
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), [2, 2]);
        $counts = $this->targetPhraseRepository->validatePhrasesSince($yesterday);
        $this->assertEquals(1, $counts['up1']);
        $this->assertEquals(3, $counts['up2']);
        $this->assertEquals(1, $counts['up3']);
    }

    function testGetNextAvailableNoLoggedInTranslations() {

        $this->addTranslationWithValidations($this->entityManager, $this->translationService, null, []);
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, null, []);

        /** @var TargetPhrase $first */
        $first = $this->targetPhraseRepository->getNextAvailable();
        /** @var TargetPhrase $second */
        $second = $this->targetPhraseRepository->getNextAvailable([$first->getId()]);

        $this->assertNotEquals($first->getId(), $second->getId());
        // expect first id to be less than second
        $this->assertLessThan($second->getId(), $first->getId());
    }

    function testGetNextAvailableLoggedInTranslation() {

        $user = $this->profileService->createUserWithCode('testuser1', 'xV5f&');
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, null, []);
        $this->addTranslationWithValidations($this->entityManager, $this->translationService, $user->getId(), []);

        /** @var TargetPhrase $first */
        $first = $this->targetPhraseRepository->getNextAvailable();
        /** @var TargetPhrase $second */
        $second = $this->targetPhraseRepository->getNextAvailable([$first->getId()]);

        $this->assertNotEquals($first->getId(), $second->getId());
        // expect second id to be less than first, as the first one returned should be prioritized because it has a
        $this->assertLessThan($first->getId(), $second->getId());
    }
}