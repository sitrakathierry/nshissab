<?php

namespace App\Repository;

use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\FactDetails;
use App\Entity\FactPaiement;
use App\Entity\PrdEntrepot;
use App\Entity\PrdEntrpAffectation;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdVariationPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CrdFinance>
 *
 * @method CrdFinance|null find($id, $lockMode = null, $lockVersion = null)
 * @method CrdFinance|null findOneBy(array $criteria, array $orderBy = null)
 * @method CrdFinance[]    findAll()
 * @method CrdFinance[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CrdFinanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CrdFinance::class);
    }

    public function save(CrdFinance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CrdFinance $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public static function comparaisonDates($a, $b) {
        $dateA = \DateTime::createFromFormat('d/m/Y', $a['date']);
        $dateB = \DateTime::createFromFormat('d/m/Y', $b['date']);
        return $dateB <=> $dateA;
    }

    public function generateSuiviGeneralCredit($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $paiement = $this->getEntityManager()->getRepository(FactPaiement::class)->findOneBy([
                "reference" => "CR"
            ]) ;
    
            $finances = $this->getEntityManager()->getRepository(CrdFinance::class)->findBy([
                "agence" => $params["agence"],
                "paiement" => $paiement,
            ]) ; 
    
            // dd($finances) ;

            $elements = [] ;
    
            foreach ($finances as $finance) {
                $facture = $finance->getFacture() ;

                if(!$facture->isStatut()) 
                    continue ;

                $financeDetails = $this->getEntityManager()->getRepository(CrdDetails::class)->findBy([
                    "finance" => $finance
                ]) ;
    
                if(empty($financeDetails))
                    continue ;

                $item2 = [] ;
                    
                foreach ($financeDetails as $financeDetail) 
                {
                    $item = [
                        "id" => $financeDetail->getId() ,
                        "idF" => $financeDetail->getFinance()->getId() ,
                        "description" => empty($financeDetail->getDescription()) ? "-" : $financeDetail->getDescription() ,
                        "date" => $financeDetail->getDate()->format('d/m/Y') ,
                        "montant" => $financeDetail->getMontant(),
                        "type" => "PAIEMENT",
                        "statut" => "OK"
                    ] ;
    
                    array_push($item2,$item) ;
                }
    
                $details = $item2 ;

                usort($details, [self::class, 'comparaisonDates']);

                $affectEntrepots = $this->getEntityManager()->getRepository(PrdEntrpAffectation::class)->findBy([
                    "agent" => $facture->getUser(),
                    "statut" => True
                ]) ;

                $factDetail = $this->getEntityManager()->getRepository(FactDetails::class)->findOneBy([
                    "facture" => $facture,
                    "statut" => True
                ]) ;

                if($factDetail->getActivite() == "Produit")
                {
                    $idVariation = $factDetail->getEntite() ;
        
                    $variation = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($idVariation) ;
        
                    if(!empty($affectEntrepots))
                    {
                        foreach ($affectEntrepots as $affectEntrepot) {
                            $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                                "variationPrix" => $factDetail->getId(),
                                "entrepot" => $affectEntrepot->getEntrepot(),
                                "statut" => True
                            ]) ;

                            if(is_null($histoEntrepot))
                                break ;
                        }

                        dd($histoEntrepot) ;
                    }
                    else
                    {
                        $entrepot = $this->getEntityManager()->getRepository(PrdEntrepot::class)->findOneBy([
                            "nom" => strtoupper($facture->getLieu()),
                            "statut" => True
                        ]) ;
                        
                        $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                            "variationPrix" => $variation,
                            "entrepot" => $entrepot,
                            "statut" => True
                        ]) ;
        
                        if(is_null($histoEntrepot))
                        {
                            $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                                "variationPrix" => $variation,
                                "statut" => True
                            ]) ;
                        }
                    }

                    $entrepot = $histoEntrepot->getEntrepot()->getNom() ;
                    $idEntrepot = $histoEntrepot->getEntrepot()->getId() ;

                }
                else
                {
                    $entrepot = "-" ;
                    $idEntrepot = "-" ;
                }
                
                if($facture->getClient()->getType()->getId() == 2)
                {
                    $client = $facture->getClient()->getClient()->getNom() ;
                    $idClient = $facture->getClient()->getId() ;
                }
                else
                {
                    $client = $facture->getClient()->getSociete()->getNom() ;
                    $idClient = $facture->getClient()->getId() ;
                }

                $financeArray = [
                    "id" => $finance->getId(),
                    "num_credit" => $finance->getNumFnc(),
                    "client" => $client,
                    "idClient" => $idClient,
                    "entrepot" => $entrepot,
                    "idEntrepot" => $idEntrepot,
                    "nbRow" => count($details) + 2,
                    "details" => $details
                ] ;

                array_push($elements,$financeArray)  ;
            }

            file_put_contents($params["filename"],json_encode($elements)) ;
        }
        
        return json_decode(file_get_contents($params["filename"])) ;
    }

//    /**
//     * @return CrdFinance[] Returns an array of CrdFinance objects
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

//    public function findOneBySomeField($value): ?CrdFinance
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
