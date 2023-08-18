<?php

namespace App\Controller\Api;


use App\Repository\SourcePhraseRepository;
use App\Repository\TargetPhraseRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StatsController extends AbstractController
{

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var UserRepository */
    private $userRepository;

    public function __construct(
        UserRepository $userRepository,
        SourcePhraseRepository $sourcePhraseRepository,
        TargetPhraseRepository $targetPhraseRepository
    )
    {
        $this->targetPhraseRepository = $targetPhraseRepository;
        $this->userRepository = $userRepository;
    }

    public function homeStats() {
        $fakeHomeStats = [
            'totalSentences' => 240000,
            'verified' => [
                'time' => 'this month',
                'up1' => 12,
                'up2' => 3,
                'up3' => 35,
            ],
            'members' => [
                'time' => 'this week',
                'value' => 42
            ]
        ];

        $verifiedCounts = $this->targetPhraseRepository->validatePhrasesSince(new \DateTime(date('Y-m-01')));
        $verifiedCounts['time'] = 'this month';

        $homeStats = [
            'totalSentences' => $this->targetPhraseRepository->totalNumberOfTargetPhrases(),
            'verified' => $verifiedCounts,
            'members' => [
                'time' => 'this month',
                'value' => $this->userRepository->newUsersSince(new \DateTime(date('Y-m-01')))
            ]
        ];
        return $this->json(['data' => $homeStats]);
    }

    public function randomStats() {
        $statsOptions = [
            [
                'time' => 'This week',
                'facts' => [
                    ['type' => 'new-users', 'text' => '55 NEW', 'value' => 55],
                    ['type' => 'new-votes3', 'text' => '18 NEW', 'value' => 18]
                ]
            ],
            [
                'time' => 'This month',
                'facts' => [
                    ['type' => 'new-users', 'text' => '155 NEW', 'value' => 55],
                    ['type' => 'new-votes3', 'text' => '412 NEW', 'value' => 18]
                ]
            ],
            [
                'time' => 'In total we have',
                'facts' => [
                    ['type' => 'current-users', 'text' => '1500', 'value' => 1500],
                    ['type' => 'current-translations', 'text' => '180', 'value' => 180]
                ]
            ],
            [
                'time' => 'In total we have',
                'facts' => [
                    ['type' => 'translated-sentences-goal', 'text' => '150000', 'value' => 150000],
                    ['type' => 'current-votes3', 'text' => '180', 'value' => 180]
                ]
            ]

        ];

        return $this->json(['data' => $statsOptions[array_rand($statsOptions)]]);
    }
}