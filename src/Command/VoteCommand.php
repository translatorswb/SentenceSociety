<?php

namespace App\Command;


use App\Entity\Rating;
use App\Repository\TargetPhraseRepository;
use App\Repository\UserRepository;
use App\Service\ITimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VoteCommand extends Command
{
    /** @var UserRepository */
    private $userRepository;

    /** @var TargetPhraseRepository */
    private $targetPhraseRepository;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ITimeService */
    private $timeService;

    public function __construct(
        UserRepository $userRepository,
        TargetPhraseRepository $targetPhraseRepository,
        EntityManagerInterface $em,
        ITimeService $timeService
    )
    {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->targetPhraseRepository = $targetPhraseRepository;
        $this->entityManager = $em;
        $this->timeService = $timeService;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:vote')

            // the short description shown while running "php bin/console list"
            ->setDescription('votes as user on translation')
            ->addArgument('user_id', InputArgument::REQUIRED, 'Id of user to vote as')
            ->addArgument('translation_id', InputArgument::REQUIRED, 'Id of translation to vote on')
            ->addArgument('vote', InputArgument::REQUIRED, 'vote 1,2,3 or -1')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userRepository->find($input->getArgument('user_id'));
        $translation = $this->targetPhraseRepository->find($input->getArgument('translation_id'));
        $rating = new Rating();
        $rating->setTimestamp($this->timeService->currentDateTime());
        $rating->setTarget($translation);
        $rating->setValue($input->getArgument('vote'));
        $this->entityManager->persist($rating);
        $this->entityManager->flush();
    }
}