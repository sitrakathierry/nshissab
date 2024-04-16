<?php

namespace App\Repository;

use App\Entity\PrdEntrpAffectation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdEntrpAffectation>
 *
 * @method PrdEntrpAffectation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdEntrpAffectation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdEntrpAffectation[]    findAll()
 * @method PrdEntrpAffectation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdEntrpAffectationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdEntrpAffectation::class);
    }

    public function save(PrdEntrpAffectation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdEntrpAffectation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PrdEntrpAffectation[] Returns an array of PrdEntrpAffectation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PrdEntrpAffectation
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
