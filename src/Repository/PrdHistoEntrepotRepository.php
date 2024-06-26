<?php

namespace App\Repository;

use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdVariationPrix;
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

    public static function comparaisonMultiple($a, $b) {
        // Comparaison par entrepot
        $result = strcmp($a['nomType'], $b['nomType']);
        
        if ($result !== 0) {
            return $result;
        }
        
        // Comparaison par categorie
        $result = strcmp($a['categorie'], $b['categorie']);
        
        if ($result !== 0) {
            return $result;
        }
        
        // Comparaison par nomType
        return strcmp($a['entrepot'], $b['entrepot']);
    }

    public function encodeChiffre($chiffre) {
        $result = dechex($chiffre) ;
        return base64_encode($result) ;
        // return $this->chiffrementCesar($result,7) ; 
    }

    public function generateStockInEntrepot($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $stockEntrepots = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findHistoActif([
                "agence" => $params["agence"],
                "statut" => True,
            ],[
                "entrepot" => "ASC"
            ]) ;
            
            $elements = [] ;
    
            
            foreach ($stockEntrepots as $stockEntrepot) {
                
                $margeType = $stockEntrepot->getVariationPrix()->getMargeType() ;
                
                if(is_null($margeType))
                {
                    $this->getEntityManager()->getRepository(PrdVariationPrix::class)->updateVariationPrix([
                        "variationPrix" => $stockEntrepot->getVariationPrix()
                    ]) ;
                }

                $prixAchat = $stockEntrepot->getVariationPrix()->getPrixAchat() ;
                $charge = $stockEntrepot->getVariationPrix()->getCharge() ;
                $marge = $stockEntrepot->getVariationPrix()->getMargeValeur() ;
                $margeCalcul = $stockEntrepot->getVariationPrix()->getMargeType()->getCalcul() ;
                
                $prixRevient = $prixAchat + $charge ;

                if($margeCalcul == 1)
                {
                    $marge = $marge ;
                    $margeVal = $marge ;
                    $margeRef = "MTN" ;
                }
                else if($margeCalcul == 100)
                {
                    $margeVal = ($prixRevient * $marge) / 100 ; 
                    $marge = $margeVal." (".$marge."%)" ;
                    $margeRef = "PRCT" ;
                }
                else if($margeCalcul == -1)
                {
                    $margeVal = $marge ; 
                    $marge = "(Coeff) ". $marge ;
                    $margeRef = "COEFF" ;
                }

                $element = [] ;
                $element["id"] = $stockEntrepot->getId() ;
                $element["idE"] = $stockEntrepot->getEntrepot()->getId() ;
                $element["idC"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getId() ;
                $element["idP"] = $stockEntrepot->getVariationPrix()->getProduit()->getId() ;
                $element["idVP"] = $stockEntrepot->getVariationPrix()->getId() ;
                $element["encodedIdVar"] = $this->encodeChiffre($stockEntrepot->getVariationPrix()->getId()) ;
                $element["entrepot"] = $stockEntrepot->getEntrepot()->getNom() ;
                $element["code"] = $stockEntrepot->getVariationPrix()->getProduit()->getCodeProduit() ;
                $element["codeProduit"] = $stockEntrepot->getVariationPrix()->getProduit()->getCodeProduit() ;
                $element["indice"] = !empty($stockEntrepot->getVariationPrix()->getIndice()) ? $stockEntrepot->getVariationPrix()->getIndice() : "-" ;
                $element["categorie"] = $stockEntrepot->getVariationPrix()->getProduit()->getPreference()->getCategorie()->getNom() ;
                $element["nom"] = $stockEntrepot->getVariationPrix()->getProduit()->getNom() ;
                $element["images"] = "-" ;
                $element["nomType"] = is_null($stockEntrepot->getVariationPrix()->getProduit()->getType()) ? "NA" : $stockEntrepot->getVariationPrix()->getProduit()->getType()->getNom() ;
                $element["stock"] = $stockEntrepot->getStock() ;
                $element["prixAchat"] = $prixAchat ;
                $element["charge"] = $charge ;
                $element["marge"] = $marge ;
                $element["margeVal"] = $margeVal ;
                $element["margeRef"] = $margeRef ;
                $element["prixVente"] = $stockEntrepot->getVariationPrix()->getPrixVente() ;
                
                array_push($elements,$element) ;
            }
    
            usort($elements, [self::class,'comparaisonMultiple']);
    
            file_put_contents($params["filename"],json_encode($elements)) ;
        }
 
        return json_decode(file_get_contents($params["filename"])) ; 
    }

    public function findHistoActif($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('phe')
            ->from(PrdHistoEntrepot::class, 'phe')
            ->join(PrdVariationPrix::class, 'pvp', 'WITH', 'pvp.id = phe.variationPrix')
            ->where('phe.agence = :agence')
            ->andWhere('pvp.statut = :statutPrix')
            ->andWhere('phe.statut = :statutEntrepot')
            ->orderBy('phe.entrepot', 'ASC')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('statutPrix', True)
            ->setParameter('statutEntrepot', True)
            ->getQuery() ;
        
        return $query->getResult();
    }

    public function getTotalStockInEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(phe.stock) as stockTotal FROM `prd_histo_entrepot`phe
        JOIN prd_variation_prix pvp ON pvp.id = phe.variation_prix_id
        WHERE phe.entrepot_id = ? AND pvp.statut = ? AND phe.statut = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["entrepot"],True,$params["statut"]]);
        return $resultSet->fetchAssociative();
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
