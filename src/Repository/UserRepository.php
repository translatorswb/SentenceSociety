<?php

namespace App\Repository;

use App\Entity\TargetPhrase;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\FetchMode;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getHighScores($max) {

        $em = $this->getEntityManager();
        $RAW_QUERY = "select user.name, sum(awarded_points.amount) as points 
from user 
left join awarded_points on user.id = awarded_points.user_id
group by user.name
order by points desc
limit {$max}";

        return $em->getConnection()->fetchAllAssociative($RAW_QUERY);

    }

    public function nrOfPendingTranslations($userId) {

        $em = $this->getEntityManager();

        $RAW_QUERY = "SELECT COUNT(target_phrase.id) AS pending_sentences 
FROM target_phrase 
WHERE target_phrase.user_id = ?  AND target_phrase.validation_state = ? ";

        return $em->getConnection()->fetchFirstColumn($RAW_QUERY, [$userId, TargetPhrase::VALIDATION_STATE_OPEN]);

    }

    public function nrOfReviewedTranslations($userId) {

        $em = $this->getEntityManager();

        $RAW_QUERY = "SELECT COUNT(target_phrase.id) AS pending_sentences 
FROM target_phrase 
WHERE target_phrase.user_id = ? AND target_phrase.validation_state != ?";

        return $em->getConnection()->fetchFirstColumn($RAW_QUERY, [$userId, TargetPhrase::VALIDATION_STATE_OPEN]);
    }

    public function votesForTranslations($userId) {

        $em = $this->getEntityManager();
        $RAW_QUERY = "SELECT target_phrase.validation_score
FROM target_phrase
WHERE target_phrase.user_id = ?";

        $result = $em->getConnection()->fetchAllAssociative($RAW_QUERY, [$userId]);

        //$result = $statement->fetchAll(FetchMode::COLUMN, 0);

        $down = 0;
        $up1 = 0;
        $up2 = 0;
        $up3 = 0;

        if ($result) {
            foreach ($result as $voteValue) {
                switch (intval($voteValue)) {
                    case -1:
                        $down += 1;
                        break;
                    case 1:
                        $up1 += 1;
                        break;
                    case 2:
                        $up2 += 1;
                        break;
                    case 3:
                        $up3 += 1;
                        break;
                    default:
                        break;
                }
            }
        };

        return [
            'down' => $down,
            'up1' => $up1,
            'up2' => $up2,
            'up3' => $up3,
        ];
    }

    public function getRankInHighscores($name) {

        $em = $this->getEntityManager();

        $RAW_QUERY = "SELECT 1 + Count(*) AS rank
FROM   (
                 SELECT    Sum(awarded_points.amount) AS points
                 FROM      user
                 LEFT JOIN awarded_points
                 ON        user.id = awarded_points.user_id
                 GROUP BY  user.name
                 ORDER BY  points DESC) AS highscores,
                 
       (         SELECT    Sum(awarded_points.amount) AS points
                 FROM      user
                 LEFT JOIN awarded_points
                 ON        user.id = awarded_points.user_id
                 WHERE     name LIKE ?) AS userscore
WHERE  highscores.points > userscore.points;";

        $result = $em->getConnection()->fetchAllAssociative($RAW_QUERY, [$name]);

       //$result = $statement->fetchAll();

        if ($result && count($result) === 1) {
            return $result[0]['rank'];
        } else {
            return -1;
        }
    }

    public function newUsersSince(\DateTime $date) {

        $em = $this->getEntityManager();

        $RAW_QUERY = "SELECT COUNT(user.id) AS user_count FROM user WHERE user.created >= ?";

        return $em->getConnection()->fetchFirstColumn($RAW_QUERY, [$date->format('Y-m-d')]);
    }
}
