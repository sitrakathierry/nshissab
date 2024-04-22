<?php

namespace App\Repository;

use App\Entity\PrdApprovisionnement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdApprovisionnement>
 *
 * @method PrdApprovisionnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdApprovisionnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdApprovisionnement[]    findAll()
 * @method PrdApprovisionnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdApprovisionnementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdApprovisionnement::class);
    }

    public function save(PrdApprovisionnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdApprovisionnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getLastApproVariationPrix($idVar)
    {
        $repository = $this->getEntityManager()->getRepository(PrdApprovisionnement::class) ; 

        $query = $repository->createQueryBuilder('e')
            ->where('e.variationPrix = :variation')
            ->andWhere('e.prixAchat IS NOT NULL')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter('variation', $idVar)
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function stockTotalVariationPrix($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as stockTotalVariation FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? ";
        // $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? AND `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["variationPrix"]]);
        // $resultSet = $stmt->executeQuery([$params["variationPrix"],$params["histoEntrepot"]]);
        return $resultSet->fetchAssociative();
    }

    public function stockTotalHistoEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `histo_entrepot_id` = ? ";
        // $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? AND `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["histoEntrepot"]]);
        // $resultSet = $stmt->executeQuery([$params["variationPrix"],$params["histoEntrepot"]]);
        return $resultSet->fetchAssociative();
    }

//    /**
//     * @return PrdApprovisionnement[] Returns an array of PrdApprovisionnement objects
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

//    public function findOneBySomeField($value): ?PrdApprovisionnement
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
