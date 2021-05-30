<?php

namespace App\Repository;

use App\Entity\JSONFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method JSONFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method JSONFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method JSONFile[]    findAll()
 * @method JSONFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class JSONFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, JSONFile::class);
    }

    // /**
    //  * @return JSONFile[] Returns an array of JSONFile objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('j.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?JSONFile
    {
        return $this->createQueryBuilder('j')
            ->andWhere('j.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
