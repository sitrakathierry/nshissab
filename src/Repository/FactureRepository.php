<?php

namespace App\Repository;

use App\Entity\FactDetails;
use App\Entity\FactModele;
use App\Entity\Facture;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdEntrepot;
use App\Entity\PrdEntrpAffectation;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdMargeType;
use App\Entity\PrdVariationPrix;
use App\Entity\SavAnnulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Facture>
 *
 * @method Facture|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facture|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facture[]    findAll()
 * @method Facture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facture::class);
    }

    public function save(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Facture $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateFactureToEntrepot($params = [])
    {
        $factModele = $this->getEntityManager()->getRepository(FactModele::class)->findOneBy([
            "reference" => "PROD"
        ]) ;

        $factures = $this->getEntityManager()->getRepository(Facture::class)->findBy([
            "agence" => $params["agence"],
            "modele" => $factModele,
            "statut" => True,
        ],[
            "id" => "DESC"
            ]
        ) ; 

        foreach ($factures as $facture) {
            $refModele = $facture->getModele()->getReference() ; 
            
            if(!is_null($facture->getEntrepot()))
                $this->verifyEntrepotFacture($facture,$params["agence"],$params["user"]);
            else
                continue ;

            $this->validUpdateFacture($facture, $params["agence"]) ;
        }

        $annulations = $this->getEntityManager()->getRepository(SavAnnulation::class)->findBy([
            "statut" => True,
            "agence" => $params["agence"],
        ]) ;

        foreach ($annulations as $annulation) {
            $facture = $annulation->getFacture() ;

            if(is_null($facture))
                continue ;

            $refModele = $facture->getModele()->getReference() ; 

            if($refModele != "PROD")
                continue ;

            if(!is_null($facture->getEntrepot()))
                $this->verifyEntrepotFacture($facture,$params["agence"],$params["user"]);
            else
                continue ;

            $this->validUpdateFacture($facture, $params["agence"]) ;
        }

    }

    public function verifyEntrepotFacture($facture, $agence, $user)
    {
        $factDetails = $this->getEntityManager()->getRepository(FactDetails::class)->findBy([
            "facture" => $facture,
            "statut" => True
        ]) ;

        foreach ($factDetails as $factDetail) {
            
            try
            {
                $variationPrix = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($factDetail->getEntite()) ;
            }
            catch(\Exception $e)
            {
                continue ;
            }

            $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                "variationPrix" => $variationPrix,
                "entrepot" => $facture->getEntrepot(),
                "statut" => True
            ]) ;

            // DEBUT REQUILIBRAGE
            if(is_null($histoEntrepot))
            {
                $histoEntrepot = new PrdHistoEntrepot() ;

                $histoEntrepot->setEntrepot($facture->getEntrepot()) ;
                $histoEntrepot->setVariationPrix($variationPrix) ;
                $histoEntrepot->setStock(1) ;
                $histoEntrepot->setStatut(True) ;
                $histoEntrepot->setAgence($agence) ;
                $histoEntrepot->setAnneeData(date('Y')) ;
                $histoEntrepot->setCreatedAt(new \DateTimeImmutable) ;
                $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;

                $this->getEntityManager()->persist($histoEntrepot) ;
                $this->getEntityManager()->flush() ;

                $approvisionnement = new PrdApprovisionnement() ;

                $margeType = $this->getEntityManager()->getRepository(PrdMargeType::class)->find(1) ; // Par défaut Montant

                $approvisionnement->setAgence($agence) ;
                $approvisionnement->setUser($user) ;
                $approvisionnement->setHistoEntrepot($histoEntrepot) ;
                $approvisionnement->setVariationPrix($variationPrix) ;
                $approvisionnement->setMargeType($margeType) ;
                $approvisionnement->setQuantite($factDetail->getQuantite()) ;
                $approvisionnement->setPrixAchat(NULL) ;
                $approvisionnement->setCharge(NULL) ;
                $approvisionnement->setMargeValeur(NULL) ;
                $approvisionnement->setPrixRevient(NULL) ;
                $approvisionnement->setPrixVente($variationPrix->getPrixVente()) ;
                $approvisionnement->setExpireeLe(NULL) ;
                $approvisionnement->setIsAuto(True) ;
                $approvisionnement->setDateAppro(\DateTime::createFromFormat('j/m/Y', date("d/m/Y"))) ;
                $approvisionnement->setDescription("Rééquilibrage de Produit Code : ".$variationPrix->getProduit()->getCodeProduit()) ;
                $approvisionnement->setCreatedAt(new \DateTimeImmutable) ;
                $approvisionnement->setUpdatedAt(new \DateTimeImmutable) ;

                $this->getEntityManager()->persist($approvisionnement) ;
                $this->getEntityManager()->flush() ;
            }
            // else if($histoEntrepot->getStock() < 0)
            // {
            //     $approvisionnement = new PrdApprovisionnement() ;

            //     $margeType = $this->getEntityManager()->getRepository(PrdMargeType::class)->find(1) ; // Par défaut Montant

            //     $approvisionnement->setAgence($agence) ;
            //     $approvisionnement->setUser($user) ;
            //     $approvisionnement->setHistoEntrepot($histoEntrepot) ;
            //     $approvisionnement->setVariationPrix($variationPrix) ;
            //     $approvisionnement->setMargeType($margeType) ;
            //     $approvisionnement->setQuantite(abs($histoEntrepot->getStock())) ;
            //     $approvisionnement->setPrixAchat(NULL) ;
            //     $approvisionnement->setCharge(NULL) ;
            //     $approvisionnement->setMargeValeur(NULL) ;
            //     $approvisionnement->setPrixRevient(NULL) ;
            //     $approvisionnement->setPrixVente($variationPrix->getPrixVente()) ;
            //     $approvisionnement->setExpireeLe(NULL) ;
            //     $approvisionnement->setIsAuto(True) ;
            //     $approvisionnement->setDateAppro(\DateTime::createFromFormat('j/m/Y', date("d/m/Y"))) ;
            //     $approvisionnement->setDescription("Rééquilibrage de Produit Code : ".$variationPrix->getProduit()->getCodeProduit()) ;
            //     $approvisionnement->setCreatedAt(new \DateTimeImmutable) ;
            //     $approvisionnement->setUpdatedAt(new \DateTimeImmutable) ;

            //     $this->getEntityManager()->persist($approvisionnement) ;
            //     $this->getEntityManager()->flush() ;
            // }
            // FIN REQUILIBRAGE
        }

    }

    public function validUpdateFacture($facture, $agence)
    {
        // DEBUT VRAI

        $entrepotObj = $this->getEntityManager()->getRepository(PrdEntrepot::class)->findOneBy([
            "nom" => strtoupper($facture->getLieu()),
            "agence" => $agence,
            "statut" => True
        ]) ;

        if(is_null($entrepotObj))
        {
            $affectEntrepot = $this->getEntityManager()->getRepository(PrdEntrpAffectation::class)->findOneBy([
                "agent" => $facture->getUser(),
                "statut" => True
            ]) ; 

            if(!is_null($affectEntrepot))
            {
                $entrepotObj = $affectEntrepot->getEntrepot() ;
            }
            else
            {
                $factDetail = $this->getEntityManager()->getRepository(FactDetails::class)->findOneByEntite([
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

                    // if($agence->getId() == 24)
                    //     dd($variation) ;

                    // if(is_null($histoEntrepot))
                    // {
                    //     $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                    //         "agence" => $agence,
                    //         "statut" => True
                    //     ]) ;
                    // }
                    
                    $entrepotObj = $histoEntrepot->getEntrepot() ;
                }
                else
                {
                    $entrepotObj = NULL ; 

                    // $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                    //     "agence" => $agence,
                    //     "statut" => True
                    // ]) ;

                    // $entrepotObj = $histoEntrepot->getEntrepot() ;
                }
            }
        }

        // FIN VRAI

        $facture->setEntrepot($entrepotObj) ;
        $this->getEntityManager()->flush() ;
    }

//    /**
//     * @return Facture[] Returns an array of Facture objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Facture
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
