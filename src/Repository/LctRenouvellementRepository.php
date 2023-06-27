<?php

namespace App\Repository;

use App\Entity\LctRenouvellement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LctRenouvellement>
 *
 * @method LctRenouvellement|null find($id, $lockMode = null, $lockVersion = null)
 * @method LctRenouvellement|null findOneBy(array $criteria, array $orderBy = null)
 * @method LctRenouvellement[]    findAll()
 * @method LctRenouvellement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LctRenouvellementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LctRenouvellement::class);
    }

    public function save(LctRenouvellement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LctRenouvellement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LctRenouvellement[] Returns an array of LctRenouvellement objects
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

//    public function findOneBySomeField($value): ?LctRenouvellement
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
