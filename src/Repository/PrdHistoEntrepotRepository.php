<?php

namespace App\Repository;

use App\Entity\PrdHistoEntrepot;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdHistoEntrepot>
 *
 * @method PrdHistoEntrepot|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdHistoEntrepot|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdHistoEntrepot[]    findAll()
 * @method PrdHistoEntrepot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdHistoEntrepotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdHistoEntrepot::class);
    }

    public function save(PrdHistoEntrepot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdHistoEntrepot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PrdHistoEntrepot[] Returns an array of PrdHistoEntrepot objects
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

//    public function findOneBySomeField($value): ?PrdHistoEntrepot
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
