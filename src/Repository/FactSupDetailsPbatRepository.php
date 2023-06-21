<?php

namespace App\Repository;

use App\Entity\FactSupDetailsPbat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactSupDetailsPbat>
 *
 * @method FactSupDetailsPbat|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactSupDetailsPbat|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactSupDetailsPbat[]    findAll()
 * @method FactSupDetailsPbat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactSupDetailsPbatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactSupDetailsPbat::class);
    }

    public function save(FactSupDetailsPbat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FactSupDetailsPbat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FactSupDetailsPbat[] Returns an array of FactSupDetailsPbat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FactSupDetailsPbat
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
