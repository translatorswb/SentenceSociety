<?php

namespace App\Command;


use App\Entity\SourcePhrase;
use App\Service\ITimeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportSourceSentencesCommand extends Command
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ITimeService */
    private $timeService;

    public function __construct(EntityManagerInterface $em, ITimeService $timeService)
    {
        parent::__construct();
        $this->entityManager = $em;
        $this->timeService = $timeService;
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:add-translation-sentences')

            // the short description shown while running "php bin/console list"
            ->setDescription('import source from 2 column csv (source, level)')
            ->addArgument('filename', InputArgument::REQUIRED, 'path to file with data to import')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $filename = $input->getArgument('filename');
        $row = 1;
        $importData = [];
        if (($handle = fopen($filename, "r")) !== FALSE) {
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
        foreach ($importData as $key => $phraseParts) {

            if (count($phraseParts) != 2) {
                echo "skipping row {$key} that doesn't have 2 columns\n";
                continue;
            }

            $item = new SourcePhrase();
            $item->setText($phraseParts[0]);
            $item->setLevel($phraseParts[1]);

            // schedule for persistence
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }
}