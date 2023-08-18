<?php
/**
 * Created by PhpStorm.
 * User: simongroenewolt
 * Date: 28/11/2018
 * Time: 12:23
 */

namespace App\Service;


use App\Entity\AwardedPoints;
use App\Entity\User;
use App\Model\TranslationSession;
use App\Repository\RatingRepository;
use App\Repository\TargetPhraseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class TranslationSessionService
{
    static $USER_TRANSLATION_SESSION_KEY = 'userTranslationSession';

    /** @var EntityManagerInterface */
    private $em;

    /** @var SessionInterface */
    private $session;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var RatingRepository */
    private $ratingRepository;

    public function __construct(
        EntityManagerInterface $em,
        SessionInterface $session,
        LoggerInterface $logger,
        TargetPhraseRepository $targetPhraseRepository,
        RatingRepository $ratingRepository
    )
    {
        $this->em = $em;
        $this->session = $session;
        $this->logger = $logger;
        $this->targetPhraseRepository = $targetPhraseRepository;
        $this->ratingRepository = $ratingRepository;
    }

    public function destroyTranslationSession() {
        $this->session->remove(self::$USER_TRANSLATION_SESSION_KEY);
        $this->session->save();
    }

    public function getOrCreateTranslationSession() {
        $translationSession = $this->session->get(self::$USER_TRANSLATION_SESSION_KEY);
        if ($translationSession === null) {
            $this->logger->info('*** creating new translationsession');
            $translationSession = new TranslationSession();
            $this->session->set(self::$USER_TRANSLATION_SESSION_KEY, $translationSession);
        } else {
            $this->logger->info('*** using existing translationsession');
        }
        return $translationSession;
    }

    public function saveSession() {
        $this->session->save();
    }

    public function addRoundBonusToUser(User $user, \DateTimeInterface $dateTime) {
        $bonus = new AwardedPoints();
        $bonus->setTimestamp($dateTime);
        $bonus->setType(AwardedPoints::TYPE_TRANSLATION_ROUND);
        $bonus->setAmount(AwardedPoints::VALUE_TRANSLATION_ROUND);
        $user->addAwardedPoint($bonus);
        $this->em->persist($user);
        $this->em->persist($bonus);
        $this->em->flush();
    }


    /**
     * note: the session should be saved after this!
     */
    public function updateTranslationSessionAfterLogin(User $user, TranslationSession $translationSession) {

        // hand out bonuses
        foreach ($translationSession->postponedBonuses() as $postponedBonus) {
            $bonusDateTime = new \DateTime();
            $bonusDateTime->setTimestamp($postponedBonus['timestamp']);
            $this->addRoundBonusToUser($user, $bonusDateTime);
        }
        // clear bonuses
        $translationSession->clearPostponedBonuses();

        // link translations
        foreach ($translationSession->getCreatedTargetPhraseAssignmentIds() as $translatedTargetId) {
            // $this->profileService->linkTargetToUser($user, $translatedTargetId);
            $target = $this->targetPhraseRepository->find($translatedTargetId);
            $target->setUser($user);
        }

        // link ratings
        foreach ($translationSession->getRatingIds() as $ratingId) {
            $rating = $this->ratingRepository->find($ratingId);
            $rating->setUser($user);
        }
        $this->em->flush();
    }
}