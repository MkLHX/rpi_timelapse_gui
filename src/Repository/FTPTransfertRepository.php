<?php

namespace App\Repository;

use App\Entity\FTPTransfert;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method FTPTransfert|null find($id, $lockMode = null, $lockVersion = null)
 * @method FTPTransfert|null findOneBy(array $criteria, array $orderBy = null)
 * @method FTPTransfert[]    findAll()
 * @method FTPTransfert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FTPTransfertRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FTPTransfert::class);
    }

    // /**
    //  * @return FTPTransfert[] Returns an array of FTPTransfert objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FTPTransfert
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
