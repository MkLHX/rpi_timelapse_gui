<?php

namespace App\Repository;

use App\Entity\Timelapse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Timelapse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Timelapse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Timelapse[]    findAll()
 * @method Timelapse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimelapseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Timelapse::class);
    }

    // /**
    //  * @return Timelapse[] Returns an array of Timelapse objects
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
    public function findOneBySomeField($value): ?Timelapse
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
