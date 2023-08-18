<?php

namespace App\Command;


use App\Entity\SourcePhrase;
use App\Entity\TargetPhrase;
use App\Service\ImportSourcePhrasesService;
use App\Service\ITimeService;
use App\Service\ProfileService;
use App\Service\TranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class InsertTestContentCommand extends Command
{

    /** @var ProfileService */
    private $profileService;

    /** @var TranslationService */
    private $translationService;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var ITimeService */
    private $timeService;

    public function __construct(ProfileService $profileService, TranslationService $translationService,
                                EntityManagerInterface $em, UserPasswordEncoderInterface $userPasswordEncoder,
                                ITimeService $timeService)
    {
        parent::__construct();
        $this->profileService = $profileService;
        $this->translationService = $translationService;
        $this->entityManager = $em;
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->timeService = $timeService;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:insert-test-content')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Inserts content.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $this->profileService->createUserWithCode('user1', 'pass123');
        $this->profileService->createUserWithCode('user2', 'pass123');
        $this->profileService->createUserWithCode('user3', 'pass123');

        // users for highscore
        $this->profileService->createUserWithCode('Kia_007', 'pass123');
        $this->profileService->addUserScore('Kia_007', 100985);

        $this->profileService->createUserWithCode('MahletKL', 'pass123');
        $this->profileService->addUserScore('MahletKL', 95437);

        $this->profileService->createUserWithCode('Areda42', 'pass123');
        $this->profileService->addUserScore('Areda42', 91276);

        $this->profileService->createUserWithCode('Essu12', 'pass123');
        $this->profileService->addUserScore('Essu12', 87620);


        // import csv data
        $row = 1;
        $importData = [];
        if (($handle = fopen("sample.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $num = count($data);
                $rowData = [];
                $row++;
                for ($c=0; $c < $num; $c++) {
                    $rowData[] = $data[$c];
                }
                $importData[] = $rowData;
            }
            fclose($handle);
        }

        //
        // insert data (temp, todo: use services!)
        //
        foreach ($importData as $phraseParts) {

            $item = new SourcePhrase();
            $item->setText($phraseParts[0]);

            $target = new TargetPhrase();
            $target->setSource($item);
            $target->setTimestamp($this->timeService->currentDateTime());
            $target->setText($phraseParts[1]);

            // schedule for persistence
            $this->entityManager->persist($item);
            $this->entityManager->persist($target);
        }

        // actually executes the queries
        $this->entityManager->flush();

    }
}