<?php

namespace App\Repository;

use App\Entity\AchHistoPaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AchHistoPaiement>
 *
 * @method AchHistoPaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchHistoPaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchHistoPaiement[]    findAll()
 * @method AchHistoPaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchHistoPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchHistoPaiement::class);
    }

    public function save(AchHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AchHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTotalPaiement($idBon)
    {
        $sql = " SELECT SUM(`montant`) as credit FROM `ach_histo_paiement` WHERE `bon_commande_id` = ? " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$idBon]);
        return $resultSet->fetchAssociative();
    }

//    /**
//     * @return AchHistoPaiement[] Returns an array of AchHistoPaiement objects
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

//    public function findOneBySomeField($value): ?AchHistoPaiement
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
