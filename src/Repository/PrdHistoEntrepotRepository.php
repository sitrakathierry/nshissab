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

    public function getProduitsInEntrepots($entrepot)
    {
        $sql = "
        SELECT p.id, p.code_produit as codeProduit, IF(p.type_id IS NULL,'NA',pt.nom) as nomType, p.nom, phe.stock FROM `prd_histo_entrepot` phe 
        JOIN prd_variation_prix pvp ON phe.variation_prix_id = pvp.id
        LEFT JOIN produit p ON p.id = pvp.produit_id
        LEFT JOIN prd_type pt ON p.type_id = pt.id
        WHERE phe.entrepot_id = ? AND p.statut = 1 AND phe.statut = 1 AND pvp.statut = 1 
        GROUP BY p.id ORDER BY p.id ASC
        " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$entrepot]);
        return $resultSet->fetchAllAssociative();
    }
    
    public function getPrixProduitsE($idE, $idP)
    {
        $sql = "
        SELECT pvp.id, pvp.prix_vente as prixVente, IF(phe.indice IS NULL,'-',phe.indice) as indice FROM `prd_histo_entrepot` phe 
        JOIN prd_variation_prix pvp ON phe.variation_prix_id = pvp.id 
        LEFT JOIN produit p ON p.id = pvp.produit_id 
        WHERE phe.entrepot_id = ? AND p.id = ? AND p.statut = 1 AND phe.statut = 1 AND pvp.statut = 1 
        ORDER BY pvp.id ASC ; 
        " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$idE,$idP]);
        return $resultSet->fetchAllAssociative();
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
