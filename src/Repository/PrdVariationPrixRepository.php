<?php

namespace App\Repository;

use App\Entity\PrdVariationPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdVariationPrix>
 *
 * @method PrdVariationPrix|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdVariationPrix|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdVariationPrix[]    findAll()
 * @method PrdVariationPrix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdVariationPrixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdVariationPrix::class);
    }

    public function save(PrdVariationPrix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdVariationPrix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // public function getProdtuiPrixParIndice($idP)
    // {
    //     $sql = "
    //     SELECT phe.id, pvp.prix_vente as prixVente, phe.indice FROM `prd_variation_prix` pvp
    //     JOIN produit p ON pvp.produit_id = p.id
    //     LEFT JOIN prd_histo_entrepot phe ON pvp.id = phe.variation_prix_id
    //     WHERE p.id = ? AND p.statut = 1 AND pvp.statut = 1 AND phe.statut = 1
    //     ORDER BY pvp.id ASC
    //     " ;
    //     $conn = $this->getEntityManager()->getConnection();
    //     $stmt = $conn->prepare($sql);
    //     $resultSet = $stmt->executeQuery([$idP]);
    //     return $resultSet->fetchAllAssociative();
    // } 
//    /**
//     * @return PrdVariationPrix[] Returns an array of PrdVariationPrix objects
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

//    public function findOneBySomeField($value): ?PrdVariationPrix
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
