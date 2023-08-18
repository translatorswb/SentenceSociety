<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class ExportService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }

    function export() {

        // export format
        // source_id, target_id, source_text, target_text, target_votes ([2,-1,3]), target_flagged (0 or bigger)

        //
        // created 2 queries as I couldn't get the result in one - feel free to improve :-)
        //

        $TARGETS_WITH_VOTES_QUERY = "SELECT
source_phrase.id AS source_id,
target_phrase.id AS target_id,
source_phrase.phrase AS source_text,
target_phrase.phrase AS target_text,
GROUP_CONCAT(rating.value) AS ratings
FROM target_phrase
LEFT JOIN source_phrase ON source_phrase.id = target_phrase.source_id
INNER JOIN rating ON target_phrase.id = rating.target_id
GROUP BY target_phrase.id;";

        $statement = $this->entityManager->getConnection()->prepare($TARGETS_WITH_VOTES_QUERY);
        $statement->execute();

        $allVoted = $statement->fetchAll();

        $FLAGGED_TARGETS_QUERY = "SELECT
target_phrase.id AS target_id,
COUNT(target_phrase_flag.id) AS flags
FROM target_phrase
LEFT JOIN target_phrase_flag ON target_phrase_flag.target_phrase_id = target_phrase.id
GROUP BY target_phrase.id
HAVING flags > 0;";

        $statement = $this->entityManager->getConnection()->prepare($FLAGGED_TARGETS_QUERY);
        $statement->execute();
        $allFlags = $statement->fetchAll();

        $flagLookup = [];
        foreach ($allFlags as $flag) {
            $flagLookup[$flag['target_id']] = $flag['flags'];
        }

        foreach ($allVoted as &$row) {
            if (isset($flagLookup[$row['target_id']])) {
                $row['flags'] = $flagLookup[$row['target_id']];
            } else {
                $row['flags'] = 0;
            }
            $row['ratings'] = "[" . $row['ratings'] . "]";
        }

        return $allVoted;
    }
}