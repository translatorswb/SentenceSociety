<?php

namespace App\Repository;

use App\Entity\SourcePhraseFlag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SourcePhraseFlag|null find($id, $lockMode = null, $lockVersion = null)
 * @method SourcePhraseFlag|null findOneBy(array $criteria, array $orderBy = null)
 * @method SourcePhraseFlag[]    findAll()
 * @method SourcePhraseFlag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SourcePhraseFlagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SourcePhraseFlag::class);
    }

    // /**
    //  * @return SourcePhraseFlag[] Returns an array of SourcePhraseFlag objects
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
    public function findOneBySomeField($value): ?SourcePhraseFlag
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
