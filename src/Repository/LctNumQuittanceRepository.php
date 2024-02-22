<?php

namespace App\Repository;

use App\Entity\LctNumQuittance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LctNumQuittance>
 *
 * @method LctNumQuittance|null find($id, $lockMode = null, $lockVersion = null)
 * @method LctNumQuittance|null findOneBy(array $criteria, array $orderBy = null)
 * @method LctNumQuittance[]    findAll()
 * @method LctNumQuittance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LctNumQuittanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LctNumQuittance::class);
    }

    public function save(LctNumQuittance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LctNumQuittance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LctNumQuittance[] Returns an array of LctNumQuittance objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LctNumQuittance
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
