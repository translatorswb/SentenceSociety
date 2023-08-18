<?php

namespace App\Command;


use App\Service\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExportTranslationsCommand extends Command
{
    /** @var ExportService */
    private $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:export-translations')

            // the short description shown while running "php bin/console list"
            ->setDescription('exports translations from the db')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $export = $this->exportService->export();
        $handle = fopen("php://output", "w");

        $len = count($export);

        for ($i = 0; $i < $len; $i++) {
            fputcsv($handle, $export[$i]);
        }

    }
}