<?php

namespace App\Repository;

use App\Entity\TargetPhrase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TargetPhrase|null find($id, $lockMode = null, $lockVersion = null)
 * @method TargetPhrase|null findOneBy(array $criteria, array $orderBy = null)
 * @method TargetPhrase[]    findAll()
 * @method TargetPhrase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TargetPhraseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TargetPhrase::class);
    }



    // /**
    //  * @return TargetPhrase[] Returns an array of TargetPhrase objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TargetPhrase
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getNextAvailable($skipIds = []) {
        try {
            $qb = $this->createQueryBuilder('s')
                ->addSelect('CASE WHEN(s.user IS NULL) THEN 0 ELSE 1 END AS HIDDEN has_user_id')
                ->andWhere('s.validationState = 0')
                ->addOrderBy('has_user_id', 'DESC')
               // ->addOrderBy('s.id', 'ASC')
		->addOrderBy('RAND()');
            if ( count( $skipIds) > 0) {
                $qb = $qb->andWhere($this->getEntityManager()->createQueryBuilder()->expr()->notIn('s.id', $skipIds));
            }

//            die ($qb->getQuery()->getSQL() . " parameters " . print_r($qb->getQuery()->getParameters()->toArray(), true));

            $next = $qb
                ->getQuery()
                ->setMaxResults(1)
                ->getOneOrNullResult();
            return $next;
        } catch (NonUniqueResultException $exception) {
            // TODO, what should we return?
            echo $exception;
            return null;
        }
    }

    public function totalNumberOfTargetPhrases() {

        $em = $this->getEntityManager();

        $RAW_QUERY = "SELECT COUNT(target_phrase.id) AS total_sentences FROM target_phrase";

        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->execute();

        $result = $statement->fetchColumn(0);
        return (int) $result;
    }

    public function validatePhrasesSince(\DateTime $date) {

        $em = $this->getEntityManager();

        $RAW_QUERY = "select distinct(validation_score) as score, count(validation_score) as `count`
from target_phrase
where target_phrase.id IN     (
            select target_id from rating WHERE rating.timestamp >= :date
  )
group by validation_score";


        $statement = $em->getConnection()->prepare($RAW_QUERY);
        $statement->bindValue('date', $date, 'datetime');
        $statement->execute();

        $counts = $statement->fetchAll();

        $result = [
            'up1' => 0,
            'up2' => 0,
            'up3' => 0,
        ];

        foreach ($counts as $count) {
            if ($count['score'] == 1) {
                $result['up1'] = $count['count'];
            } else if ($count['score'] == 2) {
                $result['up2'] = $count['count'];
            } else if ($count['score'] == 3) {
                $result['up3'] = $count['count'];
            }
        }

        return $result;
    }
}
