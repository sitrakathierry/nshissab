<?php

namespace App\Repository;

use App\Entity\CrdDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CrdDetails>
 *
 * @method CrdDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrdDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrdDetails[]    findAll()
 * @method CrdDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrdDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrdDetails::class);
    }

    public function save(CrdDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CrdDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getFinanceTotalPayee($finance)
    {
        $sql = "SELECT SUM(montant) as total FROM `crd_details` WHERE `finance_id` = ? " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$finance]);
        return $resultSet->fetchAssociative();
    }

    // $response = $this->createQueryBuilder('au')
    //         ->select('SUM(au.montant) as sommeAvoirUse')
    //         ->andWhere('au.histoClient = :histoClient')
    //         ->setParameter('histoClient', $params["histoClient"])
    //         ->getQuery()
    //         ->getSingleScalarResult();
//    /**
//     * @return CrdDetails[] Returns an array of CrdDetails objects
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

//    public function findOneBySomeField($value): ?CrdDetails
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
