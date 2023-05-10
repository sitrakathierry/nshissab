<?php

namespace App\Repository;

use App\Entity\CltTypeSociete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CltTypeSociete>
 *
 * @method CltTypeSociete|null find($id, $lockMode = null, $lockVersion = null)
 * @method CltTypeSociete|null findOneBy(array $criteria, array $orderBy = null)
 * @method CltTypeSociete[]    findAll()
 * @method CltTypeSociete[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CltTypeSocieteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CltTypeSociete::class);
    }

    public function save(CltTypeSociete $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CltTypeSociete $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return CltTypeSociete[] Returns an array of CltTypeSociete objects
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

//    public function findOneBySomeField($value): ?CltTypeSociete
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
