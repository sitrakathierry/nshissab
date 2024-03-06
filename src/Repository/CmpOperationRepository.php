<?php

namespace App\Repository;

use App\Entity\CmpOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CmpOperation>
 *
 * @method CmpOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CmpOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CmpOperation[]    findAll()
 * @method CmpOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CmpOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CmpOperation::class);
    }

    public function save(CmpOperation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CmpOperation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getSommeOperation($categorie, $compte)
    {
        $sql = "SELECT SUM(`montant`) as montant FROM `cmp_operation` WHERE `categorie_id` = ? AND `compte_id` = ? AND statut = ? " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$categorie,$compte,True]);
        return $resultSet->fetchAllAssociative();
    }

//    /**
//     * @return CmpOperation[] Returns an array of CmpOperation objects
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

//    public function findOneBySomeField($value): ?CmpOperation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
