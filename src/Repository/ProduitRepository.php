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
                $item["entrepot"] = $caissePanier->getHistoEntrepot()->getEntrepot()->getNom() ;
                $item["produit"] = $produit->getNom() ;
                $item["quantite"] = $caissePanier->getQuantite() ;
                $item["prix"] = $caissePanier->getPrix() ;
                $item["total"] = ($caissePanier->getPrix() * $caissePanier->getQuantite()) + $tva ;
                $item["type"] = "Vente" ;
                $item["indice"] = "CREDIT" ;

                array_push($listes,$item) ;
            }

            $appros = $this->getEntityManager()->getRepository(PrdApprovisionnement::class)->findBy([
                "variationPrix" => $variationPrix
            ]) ;

            foreach($appros as $appro)
            {
                if(!$appro->getHistoEntrepot()->isStatut())
                    continue ;

                $item = [] ;
                $prixVente = is_null($appro->getPrixVente()) ? $variationPrix->getPrixVente() : $appro->getPrixVente() ;
                $item["idProduit"] = $produit->getId() ;
                $item["date"] = is_null($appro->getDateAppro()) ? $appro->getCreatedAt()->format("d/m/Y") : $appro->getDateAppro()->format("d/m/Y") ;
                $item["entrepot"] = $appro->getHistoEntrepot()->getEntrepot()->getNom() ;
                $item["produit"] = $produit->getNom() ;
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

            $factureDetails = $this->getEntityManager()->getRepository(FactDetails::class)->findAllByVariation([
                "agence" => $params["agence"],    
                "variationPrix" => $variationPrix,    
                "statut" => True    
            ]) ;

            // dd($factureDetails) ;

            foreach($factureDetails as $factureDetail)
            {
                $item = [] ;
                $item["idProduit"] = $produit->getId() ;
                $item["date"] = $factureDetail->getFacture()->getDate()->format("d/m/Y");
                $item["entrepot"] = $factureDetail->getHistoEntrepot()->getEntrepot()->getNom() ; ;
                $item["produit"] = $factureDetail->getDesignation() ;
                $item["quantite"] = $factureDetail->getQuantite() ;
                $item["prix"] = $factureDetail->getPrix() ;
                $item["total"] = ($factureDetail->getPrix() * $factureDetail->getQuantite());
                $item["type"] = "Facture Definitif" ;
                $item["indice"] = "CREDIT" ;

                array_push($listes,$item) ;
            }

            $deductionVariations = $this->getEntityManager()->getRepository(PrdDeduction::class)->findBy([
                "variationPrix" => $variationPrix
            ]) ;

            // dd($deductionVariations) ;
            
            foreach ($deductionVariations as $deductionVariation) {
                $item = [] ;
                $item["idProduit"] = $produit->getId() ;
                $item["date"] = $deductionVariation->getCreatedAt()->format("d/m/Y");
                $item["entrepot"] = $deductionVariation->getHistoEntrepot()->getEntrepot()->getNom() ;
                $item["produit"] = $produit->getNom() ;
                $item["quantite"] = $deductionVariation->getQuantite() ;
                $item["prix"] = $deductionVariation->getVariationPrix()->getPrixVente() ;
                $item["total"] = ($deductionVariation->getQuantite() * $deductionVariation->getVariationPrix()->getPrixVente());
                $item["type"] = "Déduction" ;
                $item["indice"] = "CREDIT" ;

                array_push($listes,$item) ;
            }

            $savDetails = $this->getEntityManager()->getRepository(SavDetails::class)->getHistoVariationSav(
            [
                "variationPrix" => $variationPrix->getId(),
            ]) ;

            // dd($variationPrix) ;

            $listes = array_merge($listes,$savDetails) ;
        } 

        // dd($factureVariations) ;
        
        usort($listes, [self::class, 'compareDates']);

        return $listes ;
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
