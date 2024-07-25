<?php

namespace App\Repository;

use App\Entity\AgdCategorie;
use App\Entity\AgdEcheance;
use App\Entity\CrdDetails;
use App\Entity\CrdFinance;
use App\Entity\FactDetails;
use App\Entity\FactPaiement;
use App\Entity\Facture;
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

            $elements = [] ;
    
            foreach ($finances as $finance) {
                $facture = $finance->getFacture() ;

                if(!$facture->isStatut() or $facture->getType()->getReference() != 'DF') 
                    continue ;

                $refModele = $facture->getModele()->getReference() ; 

                $entrepot = "-" ;
                $idEntrepot = "-" ;
                
                if($refModele == "PROD")
                {
                    // DEBUT VRAI
    
                    $entrepotObj = $this->getEntityManager()->getRepository(PrdEntrepot::class)->findOneBy([
                        "nom" => strtoupper($facture->getLieu()),
                        "agence" => $params["agence"],
                        "statut" => True
                    ]) ;
    
                    if(!is_null($entrepotObj))
                    {
                        $entrepot = $entrepotObj->getNom();
                        $idEntrepot = $entrepotObj->getId();
                    }
                    else
                    {
                        $affectEntrepot = $this->getEntityManager()->getRepository(PrdEntrpAffectation::class)->findOneBy([
                            "agent" => $facture->getUser(),
                            "statut" => True
                        ]) ; 
        
                        if(!is_null($affectEntrepot))
                        {
                            $entrepot = $affectEntrepot->getEntrepot()->getNom();
                            $idEntrepot = $affectEntrepot->getEntrepot()->getId();
                        }
                        else
                        {
                            $factDetail = $this->getEntityManager()->getRepository(FactDetails::class)->findOneBy([
                                "facture" => $facture,
                                "activite" => "Produit",
                                "statut" => True
                            ]) ;
    
                            if(!is_null($factDetail))
                            {
                                $idVariation = $factDetail->getEntite() ;
            
                                $variation = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($idVariation) ;
    
                                $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                                    "variationPrix" => $variation,
                                    "statut" => True
                                ]) ;
    
                                $entrepot = $histoEntrepot->getEntrepot()->getNom();
                                $idEntrepot = $histoEntrepot->getEntrepot()->getId();
                            }
                            else
                            {
                                $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                                    "agence" => $this->agence,
                                    "statut" => True
                                ]) ;
    
                                $entrepot = $histoEntrepot->getEntrepot()->getNom() ;
                                $idEntrepot = $histoEntrepot->getEntrepot()->getId() ;
                            }
                        }
    
                    }
    
                    // FIN VRAI
                }
    
                if(!is_null($facture->getEntrepot()))
                {
                    if($refModele == "PROD")
                    {
                        $entrepot = is_null($facture->getEntrepot()) ? "-" : $facture->getEntrepot()->getNom();
                        $idEntrepot = is_null($facture->getEntrepot()) ? "-" : $facture->getEntrepot()->getId();
                    }
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
                    "totalTtc" => $finance->getFacture()->getTotal(),
                ] ;

                $typeRemiseG = is_null($finance->getFacture()->getRemiseType()) ? "" : $finance->getFacture()->getRemiseType()->getNotation() ;
                $typeRemiseG = ($typeRemiseG == "%") ? $typeRemiseG : "" ;

                $item2 = [] ;
                
                $financeDetails = $this->getEntityManager()->getRepository(CrdDetails::class)->findBy([
                    "finance" => $finance
                ]) ;
    
                // if(empty($financeDetails))
                //     continue ; 

                foreach ($financeDetails as $financeDetail) 
                {
                    $paiementFinance = "-" ;
                    $idPaiement = "-" ;

                    if(!is_null($financeDetail->getPaiement()))
                    {
                        $paiementFinance = $financeDetail->getPaiement()->getNom() ;
                        $idPaiement = $financeDetail->getPaiement()->getId() ;
                    }

                    $item = [
                        "id" => $financeDetail->getId() ,
                        "idF" => $financeDetail->getFinance()->getId(),
                        "description" => empty($financeDetail->getDescription()) ? "-" : $financeDetail->getDescription() ,
                        "date" => $financeDetail->getDate()->format('d/m/Y') ,
                        "currentDate" => $financeDetail->getDate()->format('d/m/Y') ,
                        "dateSuivi" => $financeDetail->getDate()->format('d/m/Y') ,
                        "dateDebut" => $financeDetail->getDate()->format('d/m/Y') ,
                        "dateFin" => $financeDetail->getDate()->format('d/m/Y') ,
                        "annee" => $financeDetail->getDate()->format('Y') ,
                        "mois" => $financeDetail->getDate()->format('m') ,
                        "montant" => $financeDetail->getMontant(),
                        "num_credit" => $finance->getNumFnc(),
                        "client" => $client,
                        "idClient" => $idClient,
                        "entrepot" => $entrepot,
                        "idEntrepot" => $idEntrepot,
                        "paiement" => $paiementFinance,
                        "idPaiement" => $idPaiement,
                        "type" => "Soldé",
                        "refType" => "PAIEMENT",
                        "statut" => "OK",
                        "totalTtc" => $finance->getFacture()->getTotal(),
                    ] ;
    
                    array_push($item2,$item) ;
                }
    
                $echeances = $this->getEntityManager()->getRepository(AgdEcheance::class)->findBy([
                    "catTable" => $finance,
                ]) ;

                $dateNow = date('d/m/Y') ;

                foreach ($echeances as $echeance) {

                    $paiementFinance = "-" ;
                    $idPaiement = "-" ;

                    if(!is_null($echeance->getPaiement()))
                    {
                        $paiementFinance = $echeance->getPaiement()->getNom() ;
                        $idPaiement = $echeance->getPaiement()->getId() ;
                    }

                    $statutEch = $echeance->isStatut() ? "OK" : (is_null($echeance->isStatut()) ? "NOT" : "DNONE") ;
                    
                    if($statutEch == "DNONE")
                        continue ;
                    
                        $dateEcheance = $echeance->getDate()->format('d/m/Y') ;
                    $isLower = $params["appService"]->compareDates($dateEcheance,$dateNow, 'P') ;

                    $item2[] = [
                        "id" => $echeance->getId() ,
                        "idF" => $finance->getId(),
                        "description" => empty($echeance->getDescription()) ? "-" : $echeance->getDescription() ,
                        "date" => $dateEcheance ,
                        "currentDate" => $dateEcheance ,
                        "dateSuivi" => $dateEcheance ,
                        "dateDebut" => $dateEcheance,
                        "dateFin" => $dateEcheance ,
                        "annee" => $echeance->getDate()->format('Y') ,
                        "mois" => $echeance->getDate()->format('m') ,
                        "montant" => $echeance->getMontant(),
                        "num_credit" => $finance->getNumFnc(),
                        "client" => $client,
                        "idClient" => $idClient,
                        "entrepot" => $entrepot,
                        "idEntrepot" => $idEntrepot,
                        "paiement" => $paiementFinance,
                        "idPaiement" => $idPaiement,
                        "type" => "Echéance",
                        "refType" => "ECH",
                        "statut" => $isLower ? "SFR" : "OK",
                        "totalTtc" => $finance->getFacture()->getTotal(),
                    ] ;
                }

                $details = $item2 ;

                usort($details, [self::class, 'comparaisonDates']);
                
                $financeArray["nbRow"] = count($details) + 2 ; 
                $financeArray["details"] = $details ;

                array_push($elements,$financeArray)  ;
            }

            file_put_contents($params["filename"],json_encode($elements)) ;
        }
        
        return json_decode(file_get_contents($params["filename"])) ;
    }

    public function findCreditDef($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('cd')
            ->from(CrdDetails::class, 'cd')
            ->join(CrdFinance::class, 'cf', 'WITH', 'cf.id = cd.finance')
            ->join(Facture::class, 'f', 'WITH', 'f.id = cf.facture')
            ->join(FactPaiement::class, 'fp', 'WITH', 'fp.id = cf.paiement')
            ->where('cf.agence = :agence')
            ->andWhere('f.statut = :statut')
            ->andWhere('fp.reference = :paiement')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('statut', $params['statut'])
            ->setParameter('paiement', $params['paiement'])
            ->getQuery() ;
        
        return $query->getResult();
    }

    public function findActive($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('cd')
            ->from(CrdDetails::class, 'cd')
            ->join(CrdFinance::class, 'cf', 'WITH', 'cf.id = cd.finance')
            ->join(Facture::class, 'f', 'WITH', 'f.id = cf.facture')
            ->join(FactPaiement::class, 'fp', 'WITH', 'fp.id = cf.paiement')
            ->where('cf.agence = :agence')
            ->andWhere('f.statut = :statut')
            ->andWhere('fp.reference = :paiement')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('statut', $params['statut'])
            ->setParameter('paiement', $params['paiement'])
            ->getQuery() ;
        
        return $query->getResult();
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
