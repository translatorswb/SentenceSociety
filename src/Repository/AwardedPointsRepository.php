<?php

namespace App\Repository;

use App\Entity\AwardedPoints;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AwardedPoints|null find($id, $lockMode = null, $lockVersion = null)
 * @method AwardedPoints|null findOneBy(array $criteria, array $orderBy = null)
 * @method AwardedPoints[]    findAll()
 * @method AwardedPoints[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AwardedPointsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AwardedPoints::class);
    }

    // /**
    //  * @return AwardedPoints[] Returns an array of AwardedPoints objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AwardedPoints
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
