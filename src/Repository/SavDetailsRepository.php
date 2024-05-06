<?php

namespace App\Repository;

use App\Entity\PrdHistoEntrepot;
use App\Entity\SavDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavDetails>
 *
 * @method SavDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavDetails[]    findAll()
 * @method SavDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavDetails::class);
    }

    public function save(SavDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SavDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function calculQuantiteVariationSav($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(IF(sd.facture_detail_id IS NULL,cp.quantite,fd.quantite)) as totalSavVariation 
            FROM `sav_details` sd 
            LEFT JOIN fact_details fd ON fd.id = sd.facture_detail_id 
            RIGHT JOIN caisse_panier cp ON cp.id = sd.caisse_detail_id 
            WHERE (cp.variation_prix_id = ? OR fd.entite = ? ) AND sd.in_stock = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["variationPrix"],
            $params["variationPrix"],
            True
        ]);
        return $resultSet->fetchAssociative();
    }

    public function getHistoVariationSav($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DATE_FORMAT(sa.date,'%d/%m/%Y') as date, 
		IF(sd.facture_detail_id IS NOT NULL, (SELECT pe.nom FROM `prd_histo_entrepot` phe 
		JOIN prd_entrepot pe ON pe.id = phe.entrepot_id WHERE phe.variation_prix_id = fd.entite LIMIT 1),(SELECT 		pe.nom FROM `prd_histo_entrepot` phe 
		JOIN prd_entrepot pe ON pe.id = phe.entrepot_id  
		WHERE phe.variation_prix_id = cp.variation_prix_id LIMIT 1)) as entrepot, 
        IF(sd.facture_detail_id IS NOT NULL,(SELECT p.nom FROM `prd_variation_prix` pvp JOIN produit p ON p.id = pvp.produit_id WHERE pvp.id = fd.entite LIMIT 1), p.nom) as produit,IF(sd.facture_detail_id IS NULL,cp.quantite,fd.quantite) as quantite, 0 as prix, 0 as total, 'RETOUR SAV' as type, 'DEBIT' as indice
            FROM `sav_details` sd 
            JOIN sav_annulation sa ON sa.id = sd.annulation_id
            LEFT JOIN fact_details fd ON fd.id = sd.facture_detail_id 
            RIGHT JOIN caisse_panier cp ON cp.id = sd.caisse_detail_id 
            RIGHT JOIN prd_variation_prix pvp ON pvp.id = cp.variation_prix_id
            JOIN produit p ON p.id = pvp.produit_id
            WHERE (cp.variation_prix_id = ? OR fd.entite = ? ) AND sd.in_stock = ? ";
        // $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? AND `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["variationPrix"],
            $params["variationPrix"],
            1
        ]);
        // $resultSet = $stmt->executeQuery([$params["variationPrix"],$params["histoEntrepot"]]);
        return $resultSet->fetchAllAssociative();
    }

    public function calculSavStockEntrepot($params = [])
    {
        $savDetails = $this->getEntityManager()->getRepository(SavDetails::class)->findBy([
            "inStock" => True,
            "agence" => $params["agence"]
        ]) ;

        $result = 0 ;

        foreach ($savDetails as $savDetail) {
            if(!is_null($savDetail->getFactureDetail()))
            {
                if($savDetail->getFactureDetail()->getactivite() == 'Produit' && !is_null($savDetail->getFactureDetail()->getEntite()))
                {
                    $entrepot = $savDetail->getFactureDetail()->getFacture()->getEntrepot() ;
                    $idVariation = $savDetail->getFactureDetail()->getEntite() ;
                    $variationPrix = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->find($idVariation);

                    if(is_null($entrepot))
                    {
                        $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                            "variationPrix" => $variationPrix,
                            "statut" => True
                        ],["id" => "ASC"]) ;
                        
                        if($histoEntrepot->getId() == $params["histoEntrepot"])
                        {
                            $result += $savDetail->getFactureDetail()->getQuantite() ;
                        }
                    }
                    else
                    {
                        $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                            "variationPrix" => $params["agence"],
                            "entrepot" => $entrepot,
                            "statut" => True
                        ]) ;

                        if(!is_null($histoEntrepot))
                        {
                            if($histoEntrepot->getId() == $params["histoEntrepot"])
                            {
                                $result += $savDetail->getFactureDetail()->getQuantite() ;
                            }
                        }
                    }
                }
            }
            else if(!is_null($savDetail->getCaisseDetail()))
            {
                if($savDetail->getCaisseDetail()->getHistoEntrepot()->getId() == $params["histoEntrepot"])
                {
                    $result += $savDetail->getCaisseDetail()->getQuantite() ;
                }
            }
        }

        return $result ;
    }
//    /**
//     * @return SavDetails[] Returns an array of SavDetails objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SavDetails
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
