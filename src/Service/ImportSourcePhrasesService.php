<?php

namespace App\Service;


use App\Entity\SourcePhrase;
use Doctrine\ORM\EntityManagerInterface;

class ImportSourcePhrasesService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    function import(array $phrases) {

        foreach ($phrases as $phrase) {
            $item = new SourcePhrase();
            $item->setText($phrase);
            // schedule for persistence
            $this->entityManager->persist($item);
        }

        // actually executes the queries
        $this->entityManager->flush();

    }
}