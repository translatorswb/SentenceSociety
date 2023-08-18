<?php

namespace App\Repository;

use App\Entity\TranslationSession;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TranslationSession|null find($id, $lockMode = null, $lockVersion = null)
 * @method TranslationSession|null findOneBy(array $criteria, array $orderBy = null)
 * @method TranslationSession[]    findAll()
 * @method TranslationSession[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TranslationSessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TranslationSession::class);
    }

    // /**
    //  * @return TranslationSession[] Returns an array of TranslationSession objects
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
    public function findOneBySomeField($value): ?TranslationSession
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
