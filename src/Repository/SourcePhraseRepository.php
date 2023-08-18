<?php

namespace App\Repository;

use App\Entity\SourcePhrase;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SourcePhrase|null find($id, $lockMode = null, $lockVersion = null)
 * @method SourcePhrase|null findOneBy(array $criteria, array $orderBy = null)
 * @method SourcePhrase[]    findAll()
 * @method SourcePhrase[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourcePhraseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SourcePhrase::class);
    }


    /*
     * thanks: http://allan-simon.github.io/blog/posts/doctrine2-order-by-count-many-to-many/
     * $builder = $this->createQueryBuilder('a');
     *   ->select('COUNT(u) AS HIDDEN nbrLikes', 'a')
     *   ->leftJoin('a.likedByUsers', 'u')
     *   ->orderBy('nbrLikes', 'DESC')
     *   ->groupBy('a')
     *   ->getQuery()
     *   ->getResult()
     */
    public function getNextAvailable($skipIds = [], $userLevel = 1) {
        try {
            $qb = $this->createQueryBuilder('s')
                ->addSelect("COUNT(t) as HIDDEN translationCount", 's')
                ->addSelect('CASE WHEN s.level = :userLevel THEN 1 ELSE 0 END AS HIDDEN matchingLevel')
                ->leftJoin('s.translations', 't')
                ->orderBy('matchingLevel', 'DESC') // prefer matching level
                ->addOrderBy('translationCount', 'ASC') // sort on least translations first
                ->addOrderBy('s.level', 'ASC') // if no matching level is available -> prefer simple sentences
		->addOrderBy('RAND()')
                ->groupBy('s')
                ->setParameter('userLevel', $userLevel);
            if ( count( $skipIds) > 0) {
                $qb = $qb->andWhere($this->getEntityManager()->createQueryBuilder()->expr()->notIn('s.id', $skipIds));
            }


            $query = $qb
                ->getQuery()
                ->setMaxResults(1);

            // var_dump($query->getSQL());

            $next = $query
//                ->getOneOrNullResult(Query::HYDRATE_ARRAY);
                ->getOneOrNullResult(Query::HYDRATE_OBJECT);

            return $next;
        } catch (NonUniqueResultException $exception) {
            // TODO, what should we return?
            echo $exception;
            return null;
        }
    }

    // /**
    //  * @return SourcePhrase[] Returns an array of SourcePhrase objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SourcePhrase
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
