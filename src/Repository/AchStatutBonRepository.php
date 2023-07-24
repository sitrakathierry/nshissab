<?php

namespace App\Repository;

use App\Entity\AchStatutBon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AchStatutBon>
 *
 * @method AchStatutBon|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchStatutBon|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchStatutBon[]    findAll()
 * @method AchStatutBon[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchStatutBonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchStatutBon::class);
    }

    public function save(AchStatutBon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AchStatutBon $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AchStatutBon[] Returns an array of AchStatutBon objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AchStatutBon
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
