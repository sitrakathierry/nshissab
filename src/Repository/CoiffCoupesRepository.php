<?php

namespace App\Repository;

use App\Entity\CoiffCoupes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoiffCoupes>
 *
 * @method CoiffCoupes|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoiffCoupes|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoiffCoupes[]    findAll()
 * @method CoiffCoupes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoiffCoupesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoiffCoupes::class);
    }

    public function save(CoiffCoupes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoiffCoupes $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CoiffCoupes[] Returns an array of CoiffCoupes objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?CoiffCoupes
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
