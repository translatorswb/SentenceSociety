<?php

namespace App\Repository;

use App\Entity\TargetPhraseFlag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TargetPhraseFlag|null find($id, $lockMode = null, $lockVersion = null)
 * @method TargetPhraseFlag|null findOneBy(array $criteria, array $orderBy = null)
 * @method TargetPhraseFlag[]    findAll()
 * @method TargetPhraseFlag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TargetPhraseFlagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TargetPhraseFlag::class);
    }

    // /**
    //  * @return TargetPhraseFlag[] Returns an array of TargetPhraseFlag objects
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
    public function findOneBySomeField($value): ?TargetPhraseFlag
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
