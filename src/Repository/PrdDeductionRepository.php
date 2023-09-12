<?php

namespace App\Repository;

use App\Entity\PrdDeduction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdDeduction>
 *
 * @method PrdDeduction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdDeduction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdDeduction[]    findAll()
 * @method PrdDeduction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdDeductionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdDeduction::class);
    }

    public function save(PrdDeduction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdDeduction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getSommeDeductionEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as sommeStock FROM `prd_deduction` WHERE `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["histoEntrepot"]]);
        return $resultSet->fetchAssociative();
    }
//    /**
//     * @return PrdDeduction[] Returns an array of PrdDeduction objects
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

//    public function findOneBySomeField($value): ?PrdDeduction
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
