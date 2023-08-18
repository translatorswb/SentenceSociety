<?php

namespace App\Service;


use App\Entity\AwardedPoints;
use App\Entity\User;
use App\Model\TranslationSession;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ProfileService
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var UserPasswordEncoderInterface */
    private $encoder;

    /** @var UserRepository */
    private $userRepository;

    /** @var TranslationSessionService */
    private $translationSessionService;

    /** @var ITimeService */
    private $timeService;

    private $FAKE_PROFILE;

    // aanname 1 sessie = 5 zinnen
    // dan kan je 10 duimpjes halen als je allemaal +2's hebt
    // uitrekenen -> ongeveer 10 duimpjes naar beneden
    public static $RANK_BREAKPOINTS = [
        0, // 'Beginner',
        12, // 'Junior',
        40, // 'Trainee',
        80, // 'Talent',

        130, // 'Intermediate',
        190, // 'Star',
        260, // 'Expert',
        320, // 'Master',

        400, // 'Mentor',
        500, // 'Champion',
        700, // 'Legend',
        1000, // 'Wizard',
    ];

    public static $DOWN_THUMB_MULTIPLIER = 15;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        UserRepository $userRepository,
        TranslationSessionService $translationSessionService,
        ITimeService $timeService
    )
    {
        $this->em = $em;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
        $this->translationSessionService = $translationSessionService;
        $this->timeService = $timeService;


        $this->FAKE_PROFILE = [
            'profile' => [
                'translations' => 1200,
                'points' => 3300,
                'rank' => 4,
            ],
            'highscores' => [
                [
                    'name' => 'user 1',
                    'points' => 120000
                ],
                [
                    'name' => 'user 1',
                    'points' => 22000
                ],
                [
                    'name' => 'user ምት',
                    'points' => 1200
                ]
            ],
            'scorehistory' => [
                'lastsession' => 1200,
                'periodname' => 'November',
                'periodpoints' => 2440,
            ]
        ];
    }

    public function createUserWithCode($name, $code, $email, $personalName, $country):User {

        $user = new User();
        $user->setName($name);
        $user->setPassword($this->encoder->encodePassword($user, $code));
        $user->setCreated($this->timeService->currentDateTime());
        $user->setEmail($email);
        $user->setPersonalname($personalName);
        $user->setCountry($country);

        //
        // sign-up bonus
        //
        $bonus = new AwardedPoints();
        $bonus->setType(AwardedPoints::TYPE_SIGNED_UP);
        $bonus->setTimestamp($this->timeService->currentDateTime());
        $bonus->setAmount(AwardedPoints::VALUE_SIGNED_UP);
        $user->addAwardedPoint($bonus);
        $this->em->persist($bonus);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function addUserScore($name, $score) {

        $user = $this->userRepository->findOneBy(['name' => $name]);
        $points = new AwardedPoints();
        $points->setAmount($score);
        $points->setType(AwardedPoints::TYPE_OTHER);
        $points->setTimestamp($this->timeService->currentDateTime());
        $user->addAwardedPoint($points);
        $this->em->persist($points);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function setUserRank($name, $rank) {

        $user = $this->userRepository->findOneBy(['name' => $name]);
        $user->setRank($rank);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function setUserEmailById($id, $email) {

        $user = $this->userRepository->find($id);
        $user->setEmail($email);

        $this->em->persist($user);
        $this->em->flush();
    }
    public function setUserPhoneById($id, $phone) {

        $user = $this->userRepository->find($id);
        $user->setPhone($phone);

        $this->em->persist($user);
        $this->em->flush();
    }

    public function setUserCountryById($id, $country) {

        $user = $this->userRepository->find($id);
        $user->setCountry($country);

        $this->em->persist($user);
        $this->em->flush();
    }


    public function getHighscores() {
        return $this->userRepository->getHighScores(3);
    }

    public function getUserLevelFromRank ($rank) {
        if ($rank >= 8) {
            $result = 3;
        } else if ($rank >= 4) {
            $result = 2;
        } else {
            $result = 1;
        }
        return $result;
    }

    public function getProfileData(User $user) {
        $highscores = $this->getHighscores();
        $points = 0;
        $pointsThisMonth = 0;
        $dateStartMonth = new \DateTime('first day of this month');
        foreach ($user->getAwardedPoints() as $awarded) {
            $points += $awarded->getAmount();
            if ($awarded->getTimestamp() > $dateStartMonth) {
                $pointsThisMonth += $awarded->getAmount();
            }
        }


        $votesReceived = $this->userRepository->votesForTranslations($user->getId());
//        $votesReceived = [
//            'down' => 1,
//            'up1' => 0,
//            'up2' => 17,
//            'up3' => 8,
//        ];

        /*
         * 'down' => $down,
         *  'up1' => $up1,
         *  'up2' => $up2,
         *  'up3' => $up3,
         */
        $votesTotal =
            3 * $votesReceived['up3']
            + 2 * $votesReceived['up2']
            - ProfileService::$DOWN_THUMB_MULTIPLIER * $votesReceived['down'];


        // rank 0 (newby) .. 11 (wizard)
        // count down loop
        $rank = 0;
        for ($idx = count(ProfileService::$RANK_BREAKPOINTS) - 1; $idx > 0; $idx -= 1) {
            if (ProfileService::$RANK_BREAKPOINTS[$idx] <= $votesTotal) {
                $rank = $idx;
                break;
            }
        }

        $votesToNextRank = 0;
        $votesToPreviousRank = 0;
        $currentRankBreakpoint = ProfileService::$RANK_BREAKPOINTS[$rank];
        if ($rank < 11) {
            $nextRankBreakpoint = ProfileService::$RANK_BREAKPOINTS[$rank + 1];
            $votesToNextRank = $nextRankBreakpoint - $votesTotal;
        }
        if ($rank > 0) {
            $prevRankBreakpoint = ProfileService::$RANK_BREAKPOINTS[$rank - 1];
            $votesToPreviousRank = $votesTotal - $prevRankBreakpoint;
        }

        // add 'votes to next rank'
        $votesReceived['up2ToNext'] = $rank === 11 ? 0 : ceil($votesToNextRank / 2);
        $votesReceived['up3ToNext'] = $rank === 11 ? 0 : ceil($votesToNextRank / 3);
        $votesReceived['downToNext'] = $rank === 0 ? 0 : ceil($votesToPreviousRank / ProfileService::$DOWN_THUMB_MULTIPLIER);

        return array_replace($this->FAKE_PROFILE, [
            'name' => $user->getName(),
            'verifiedEmail' => $user->getVerifiedEmail(),
            'highscores' => $highscores,
            'profile' => [
                'translations' => count($user->getTargetPhrases()),
                'points' => $points,
                'rank' => $rank,
                'level' => $this->getUserLevelFromRank($rank),
                'highscorePosition' => $this->getRankInHighscores($user->getName()),
                'votes' => $votesReceived,
                'translationsPending' => $this->userRepository->nrOfPendingTranslations($user->getId()),
                'translationsReviewed' => $this->userRepository->nrOfReviewedTranslations($user->getId()),
                'pointsInTermValue' => $pointsThisMonth,
                'pointsInTermText' => 'This month',
            ]]);
    }

    public function getRankInHighscores($name) {
        return $this->userRepository->getRankInHighscores($name);
    }

    public function checkNameAvailable($name) {
        $check = $this->userRepository->findOneBy(['name' => $name]);
        return $check === null;
    }

    public function isPasswordValid($user, $pass) {
        return $this->encoder->isPasswordValid($user, $pass);
    }

    public function requestBonus(TranslationSession $translationSession) {

        //
        // todo check if this is a valid bonus request (one translation and 2 validations since previous round request)
        //

        $userId = $translationSession->getUserId();
        if ($userId) {

            $user = $this->userRepository->find($userId);

            // get all bonuses
            $roundBonuses = $user->getAwardedPoints()->filter(function(AwardedPoints $awardedPoints) {
                return $awardedPoints->getType() === AwardedPoints::TYPE_TRANSLATION_ROUND;
            });

            $this->translationSessionService->addRoundBonusToUser($user, $this->timeService->currentDateTime());

            if (!$roundBonuses->isEmpty())
            {
                /** @var \DateTime $lastRoundBonusDay */
                $lastRoundBonusDay = clone $roundBonuses->last()->getTimestamp();
                $lastRoundBonusDay->setTime(0, 0);
                /** @var \DateTime $today */
                $today =$this->timeService->currentDateTime();
                $today->setTime(0, 0);

                $interval = $lastRoundBonusDay->diff($today);

                $days = $interval->days;
                if ($days === 1) {
                    $dayBonus = new AwardedPoints();
                    $dayBonus->setTimestamp($this->timeService->currentDateTime());
                    $dayBonus->setType(AwardedPoints::TYPE_CONSECUTIVE_DAY);
                    $dayBonus->setAmount(AwardedPoints::VALUE_CONSECUTIVE_DAY);
                    $user->addAwardedPoint($dayBonus);
                    $this->em->persist($dayBonus);
                }
            }

            $this->em->flush();

        } else {
            $translationSession->rememberBonus($this->timeService->currentDateTime());
        }
    }
}