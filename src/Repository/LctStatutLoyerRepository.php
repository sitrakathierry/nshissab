<?php

namespace App\Repository;

use App\Entity\LctStatutLoyer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LctStatutLoyer>
 *
 * @method LctStatutLoyer|null find($id, $lockMode = null, $lockVersion = null)
 * @method LctStatutLoyer|null findOneBy(array $criteria, array $orderBy = null)
 * @method LctStatutLoyer[]    findAll()
 * @method LctStatutLoyer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LctStatutLoyerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LctStatutLoyer::class);
    }

    public function save(LctStatutLoyer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LctStatutLoyer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LctStatutLoyer[] Returns an array of LctStatutLoyer objects
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

//    public function findOneBySomeField($value): ?LctStatutLoyer
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
