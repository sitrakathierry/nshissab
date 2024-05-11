<?php

namespace App\Repository;

use App\Entity\CaissePanier;
use App\Entity\FactDetails;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdDeduction;
use App\Entity\PrdType;
use App\Entity\PrdVariationPrix;
use App\Entity\Produit;
use App\Entity\SavDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateProduitStockGeneral($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $stockGenerales = $this->getEntityManager()->getRepository(Produit::class)->findBy([
                "agence" => $params["agence"],
                "statut" => True,
            ]) ;
            
            $elements = [] ;
    
            foreach ($stockGenerales as $stockGeneral) {
                $element = [] ;
    
                $element["id"] = $stockGeneral->getId() ;
                $element["encodedId"] = $this->encodeChiffre($stockGeneral->getId()) ;
                $element["idC"] = $stockGeneral->getPreference()->getId() ;
                $element["idCat"] = $stockGeneral->getPreference()->getCategorie()->getId() ;
                $element["codeProduit"] = $stockGeneral->getCodeProduit() ;
                $element["categorie"] = $stockGeneral->getPreference()->getCategorie()->getNom() ;
                $element["nom"] = $stockGeneral->getNom() ;
                $element["stock"] = $stockGeneral->getStock() ;
                $element["tvaType"] = is_null($stockGeneral->getTvaType()) ? "-" : $stockGeneral->getTvaType()->getId() ;
                $element["agence"] = $stockGeneral->getAgence()->getId() ;
                $element["type"] = is_null($stockGeneral->getType()) ? "NA" : $stockGeneral->getType()->getId() ;
                $element["nomType"] = is_null($stockGeneral->getType()) ? "NA" : $stockGeneral->getType()->getNom() ;
                $element["images"] = is_null($stockGeneral->getImages()) ? "-" : $stockGeneral->getImages() ;
    
                array_push($elements,$element) ;
            }
    
            file_put_contents($params["filename"],json_encode($elements)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ;
    }

    public function generateSuiviProduit($params = [])
    {
        $produit = $this->getEntityManager()->getRepository(Produit::class)->find($params["idProduit"]) ;

        $variationPrixs = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->findBy([
            "produit" => $produit,
            "statut" => True,
        ],[
            "id" => "DESC"
        ]) ; 

        $listes = [] ;

        foreach($variationPrixs as $variationPrix)
        {
            if($params["typeSuivi"] == "VENTE")
            {
                $caissePaniers = $this->getEntityManager()->getRepository(CaissePanier::class)->findBy([
                    "variationPrix" => $variationPrix,
                    "statut" => True 
                ]) ;
    
                foreach($caissePaniers as $caissePanier)
                {
                    $item = [] ;
                    $tva = $caissePanier->getTva() != 0 ? ($caissePanier->getPrix() * $caissePanier->getQuantite() * $caissePanier->getTva())/100 : 0 ;
                    $item["idProduit"] = $produit->getId() ;
                    $item["date"] = $caissePanier->getCommande()->getDate()->format("d/m/Y") ;
                    $item["currentDate"] = $caissePanier->getCommande()->getDate()->format('d/m/Y') ;
                    $item["dateFacture"] = $caissePanier->getCommande()->getDate()->format('d/m/Y')  ;
                    $item["dateDebut"] = $caissePanier->getCommande()->getDate()->format('d/m/Y') ;
                    $item["dateFin"] = $caissePanier->getCommande()->getDate()->format('d/m/Y') ;
                    $item["annee"] = $caissePanier->getCommande()->getDate()->format('Y') ;
                    $item["mois"] = $caissePanier->getCommande()->getDate()->format('m') ;
                    $item["entrepot"] = $caissePanier->getHistoEntrepot()->getEntrepot()->getNom() ;
                    $item["idE"] = $caissePanier->getHistoEntrepot()->getEntrepot()->getId() ;
                    $item["produit"] = $produit->getNom() ;
                    $item["indiceP"] = $variationPrix->getIndice() ;
                    $item["quantite"] = $caissePanier->getQuantite() ;
                    $item["prix"] = $caissePanier->getPrix() ;
                    $item["total"] = ($caissePanier->getPrix() * $caissePanier->getQuantite()) + $tva ;
                    $item["type"] = "Vente" ;
                    $item["indice"] = "CREDIT" ;
    
                    array_push($listes,$item) ;
                }

                $factureDetails = $this->getEntityManager()->getRepository(FactDetails::class)->findAllByVariation([
                    "agence" => $params["agence"],    
                    "variationPrix" => $variationPrix,    
                    "statut" => True    
                ]) ;
        
                foreach($factureDetails as $factureDetail)
                {
                    $item = [] ;
                    $item["idProduit"] = $produit->getId() ;
                    $item["date"] = $factureDetail->getFacture()->getDate()->format("d/m/Y");
                    $item["currentDate"] = $factureDetail->getFacture()->getDate()->format('d/m/Y') ;
                    $item["dateFacture"] = $factureDetail->getFacture()->getDate()->format('d/m/Y')  ;
                    $item["dateDebut"] = $factureDetail->getFacture()->getDate()->format('d/m/Y') ;
                    $item["dateFin"] = $factureDetail->getFacture()->getDate()->format('d/m/Y') ;
                    $item["annee"] = $factureDetail->getFacture()->getDate()->format('Y') ;
                    $item["mois"] = $factureDetail->getFacture()->getDate()->format('m') ;
                    $item["entrepot"] = $factureDetail->getHistoEntrepot()->getEntrepot()->getNom() ; ;
                    $item["idE"] = $factureDetail->getHistoEntrepot()->getEntrepot()->getId() ; ;
                    $item["produit"] = $factureDetail->getDesignation() ;
                    $item["indiceP"] = $variationPrix->getIndice() ;
                    $item["quantite"] = $factureDetail->getQuantite() ;
                    $item["prix"] = $factureDetail->getPrix() ;
                    $item["total"] = ($factureDetail->getPrix() * $factureDetail->getQuantite());
                    $item["type"] = "Vente" ;
                    $item["indice"] = "CREDIT" ;
    
                    array_push($listes,$item) ;
                }
            }

            if($params["typeSuivi"] == "APPRO")
            {
                $appros = $this->getEntityManager()->getRepository(PrdApprovisionnement::class)->findBy([
                    "variationPrix" => $variationPrix
                ]) ;
    
                foreach($appros as $appro)
                {
                    if(!$appro->getHistoEntrepot()->isStatut())
                        continue ;
                    
                    $dateAppro = is_null($appro->getDateAppro()) ? $appro->getCreatedAt() : $appro->getDateAppro() ;

                    $item = [] ;
                    $prixVente = is_null($appro->getPrixVente()) ? $variationPrix->getPrixVente() : $appro->getPrixVente() ;
                    $item["idProduit"] = $produit->getId() ;
                    $item["date"] = $dateAppro->format("d/m/Y") ;
                    $item["currentDate"] = $dateAppro->format('d/m/Y') ;
                    $item["dateFacture"] = $dateAppro->format('d/m/Y')  ;
                    $item["dateDebut"] = $dateAppro->format('d/m/Y') ;
                    $item["dateFin"] = $dateAppro->format('d/m/Y') ;
                    $item["annee"] = $dateAppro->format('Y') ;
                    $item["mois"] = $dateAppro->format('m') ;
                    $item["entrepot"] = $appro->getHistoEntrepot()->getEntrepot()->getNom() ;
                    $item["idE"] = $appro->getHistoEntrepot()->getEntrepot()->getId() ;
                    $item["produit"] = $produit->getNom() ;
                    $item["indiceP"] = $variationPrix->getIndice() ;
                    $item["quantite"] = $appro->getQuantite() ;
                    $item["prix"] = $prixVente ;
                    $item["total"] = ($prixVente * $appro->getQuantite());
                    $item["type"] = "Approvisionnement" ;
                    $item["indice"] = "DEBIT" ;
    
                    if($appro->isIsAuto())
                    {
                        $appro->getVariationPrix()->getProduit()->setToUpdate(True) ;
                        $appro->setIsAuto(False) ;
                        $this->getEntityManager()->flush() ;
                    }
    
                    array_push($listes,$item) ; 
                }

                $savDetails = $this->getEntityManager()->getRepository(SavDetails::class)->getHistoVariationSav(
                [
                    "variationPrix" => $variationPrix->getId(),
                ]) ;
    
                $listes = array_merge($listes,$savDetails) ;
            }

            if($params["typeSuivi"] == "DEDUIT")
            {
                $deductionVariations = $this->getEntityManager()->getRepository(PrdDeduction::class)->findBy([
                    "variationPrix" => $variationPrix
                ]) ;
                    
                foreach ($deductionVariations as $deductionVariation) {
                    $item = [] ;
                    $item["idProduit"] = $produit->getId() ;
                    $item["date"] = $deductionVariation->getCreatedAt()->format("d/m/Y");
                    $item["currentDate"] = $deductionVariation->getCreatedAt()->format('d/m/Y') ;
                    $item["dateFacture"] = $deductionVariation->getCreatedAt()->format('d/m/Y')  ;
                    $item["dateDebut"] = $deductionVariation->getCreatedAt()->format('d/m/Y') ;
                    $item["dateFin"] = $deductionVariation->getCreatedAt()->format('d/m/Y') ;
                    $item["annee"] = $deductionVariation->getCreatedAt()->format('Y') ;
                    $item["mois"] = $deductionVariation->getCreatedAt()->format('m') ;
                    $item["entrepot"] = $deductionVariation->getHistoEntrepot()->getEntrepot()->getNom() ;
                    $item["idE"] = $deductionVariation->getHistoEntrepot()->getEntrepot()->getId() ;
                    $item["produit"] = $produit->getNom() ;
                    $item["indiceP"] = $variationPrix->getIndice() ;
                    $item["quantite"] = $deductionVariation->getQuantite() ;
                    $item["prix"] = $deductionVariation->getVariationPrix()->getPrixVente() ;
                    $item["total"] = ($deductionVariation->getQuantite() * $deductionVariation->getVariationPrix()->getPrixVente());
                    $item["type"] = "Déduction" ;
                    $item["indice"] = "CREDIT" ;
    
                    array_push($listes,$item) ;
                }
            }
        } 
        
        usort($listes, [self::class, 'compareDates']);

        return $listes ;
    }

    public function findLastId($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT * FROM `produit` WHERE `agence_id` = ? AND `statut` = ? ORDER BY id DESC LIMIT 1 ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["agence"]->getId(),
            $params["statut"]
        ]);
        return $resultSet->fetchAssociative();
    }

    public static function compareDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }

//    /**
//     * @return Produit[] Returns an array of Produit objects
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

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
