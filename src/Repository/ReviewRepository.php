<?php

namespace App\Repository;

use App\Entity\Review;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Review|null find($id, $lockMode = null, $lockVersion = null)
 * @method Review|null findOneBy(array $criteria, array $orderBy = null)
 * @method Review[]    findAll()
 * @method Review[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }


    public function avg($article)
    {
        return $this->createQueryBuilder('r')
            ->select('AVG(r.rating)')
            ->andWhere('r.article = :article')
            ->setParameter('article',$article)
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function longeur($value,$article)
    {
        return $this->createQueryBuilder('r')
            ->select('r.rating')
            ->andWhere('r.rating = :value')
            ->andWhere('r.article = :article')
            ->setParameter('value', $value)
            ->setParameter('article', $article)
            ->getQuery()
            ->getScalarResult()
        ;
    }

    /*
    public function findOneBySomeField($value): ?Review
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
