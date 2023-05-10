<?php

namespace App\Repository;

use App\Entity\FactHistoPaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactHistoPaiement>
 *
 * @method FactHistoPaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactHistoPaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactHistoPaiement[]    findAll()
 * @method FactHistoPaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactHistoPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactHistoPaiement::class);
    }

    public function save(FactHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FactHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return FactHistoPaiement[] Returns an array of FactHistoPaiement objects
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

//    public function findOneBySomeField($value): ?FactHistoPaiement
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
