<?php

namespace App\Repository;

use App\Entity\PrdApprovisionnement;
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

    public function updateVariationPrix($params = [])
    {
        $approVariation = $this->getEntityManager()->getRepository(PrdApprovisionnement::class)->findOneBy([
            "variationPrix" => $params["variationPrix"],
        ],["id" => "ASC"]) ;

        $prixAchat = $approVariation->getPrixAchat() ;
        $charge = $approVariation->getCharge() ;
        $montantMarge = $approVariation->getMargeValeur() ;
        $margeType = $approVariation->getMargeType() ;

        $variationPrix = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($params["variationPrix"]->getId()) ;

        $variationPrix->setPrixAchat($prixAchat) ;
        $variationPrix->setCharge($charge) ;
        $variationPrix->setMargeValeur($montantMarge) ;
        $variationPrix->setMargeType($margeType) ;

        $this->getEntityManager()->flush() ;
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

    public function getVariationParEntrepot($params = [])
    {
        $sql = "
            SELECT pvp.id, pvp.prix_vente, IF(pvp.indice IS NULL,'-',pvp.indice) as indice, phe.stock FROM `prd_variation_prix` pvp 
            JOIN produit p ON p.id = pvp.produit_id 
            LEFT JOIN prd_histo_entrepot phe ON phe.variation_prix_id = pvp.id 
            RIGHT JOIN prd_entrepot pe ON pe.id = phe.entrepot_id 
            WHERE phe.entrepot_id = ? AND p.id = ? AND pvp.statut = ? AND phe.statut = ?
        " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["entrepot"],
            $params["produit"],
            1,
            1
        ]) ;
        return $resultSet->fetchAllAssociative() ;
    }

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
