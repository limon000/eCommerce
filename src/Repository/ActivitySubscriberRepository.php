<?php

namespace App\Repository;

use App\Entity\ActivitySubscriber;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method ActivitySubscriber|null find($id, $lockMode = null, $lockVersion = null)
 * @method ActivitySubscriber|null findOneBy(array $criteria, array $orderBy = null)
 * @method ActivitySubscriber[]    findAll()
 * @method ActivitySubscriber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivitySubscriberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivitySubscriber::class);
    }

    // /**
    //  * @return ActivitySubscriber[] Returns an array of ActivitySubscriber objects
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
    public function findOneBySomeField($value): ?ActivitySubscriber
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
