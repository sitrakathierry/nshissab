<?php

namespace App\Repository;

use App\Entity\FactDetails;
use App\Entity\Facture;
use App\Entity\PrdEntrepot;
use App\Entity\PrdEntrpAffectation;
use App\Entity\PrdHistoEntrepot;
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
        $factures = $this->getEntityManager()->getRepository(Facture::class)->findBy([
            "agence" => $params["agence"],
            "statut" => True,
        ],[
            "id" => "DESC"
            ]
        ) ; 

        foreach ($factures as $facture) {
            $refModele = $facture->getModele()->getReference() ; 

            if($refModele != "PROD" || !is_null($facture->getEntrepot()))
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

            if($refModele != "PROD" || !is_null($facture->getEntrepot()))
                continue ;

            $this->validUpdateFacture($facture, $params["agence"]) ;
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
                    $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                        "agence" => $agence,
                        "statut" => True
                    ]) ;

                    $entrepotObj = $histoEntrepot->getEntrepot() ;
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
