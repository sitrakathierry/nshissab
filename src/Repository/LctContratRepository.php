<?php

namespace App\Repository;

use App\Entity\LctContrat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LctContrat>
 *
 * @method LctContrat|null find($id, $lockMode = null, $lockVersion = null)
 * @method LctContrat|null findOneBy(array $criteria, array $orderBy = null)
 * @method LctContrat[]    findAll()
 * @method LctContrat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LctContratRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LctContrat::class);
    }

    public function save(LctContrat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LctContrat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateRecetteLocation($params = [])
    {
        $contrats = $this->getEntityManager()->getRepository(LctContrat::class)->generateLocationContrat([
            "agence" => $params["agence"],
            "filename" => $params["filename"],
        ]) ;

        $search = [
            "refStatut" => "ENCR",
        ] ;

        $contrats = $params["appService"]->searchData($contrats,$search) ;

        $elements = [] ;

        foreach ($contrats as $contrat) 
        {
            $id = $contrat->id ;

            $filename = "files/systeme/prestations/location/releveloyer(agence)/relevePL_".$id."_".$params["nameAgence"]  ;
            
            if(!file_exists($filename))
                $params["appService"]->generateLctRelevePaiementLoyer($filename,$id) ;
    
            $relevePaiements = json_decode(file_get_contents($filename)) ;

            $countReleve = count($relevePaiements) ;

            $totalRelevePayee = 0 ;

            for($i = 0; $i < $countReleve; $i++)
            {
                $totalRelevePayee += $relevePaiements[$i]->datePaiement != "-" ? $relevePaiements[$i]->montant : 0 ;

                if($relevePaiements[$i]->datePaiement == "-")
                {
                    break ;
                }
            }

            if($totalRelevePayee > 0)
            {
                $elements[] = [
                    "id" => $id,
                    "date" => $contrat->dateContrat,
                    "currentDate" => $contrat->dateContrat,
                    "dateFacture" => $contrat->dateContrat,
                    "dateDebut" => $contrat->dateContrat,
                    "dateFin" => $contrat->dateContrat,
                    "annee" => explode("/",$contrat->dateContrat)[2],
                    "mois" => explode("/",$contrat->dateContrat)[1],
                    "numero" => $contrat->numContrat,
                    "montant" => $totalRelevePayee,
                    "entrepot" => "-",
                    "refEntrepot" => "-",
                    "typePaiement" => "-",
                    "refTypePaiement" => "-",
                    "recette" => "Prestation Location",
                    "refRecette" => "LOCATION",
                ] ;
            }
        }

        return $elements ;
    }

    public function generateLocationContrat($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $contrats = $this->getEntityManager()->getRepository(LctContrat::class)->findBy([
                "agence" => $params["agence"],
                "statutGen" => True,
            ]) ;
    
            $items = [] ;
    
            foreach ($contrats as $contrat) {
                $item = [] ;
                if($contrat->getPeriode()->getReference() == "J")
                {
                    $periode = "Jour(s)" ;
                }
                if($contrat->getPeriode()->getReference() == "M")
                {
                    $periode = "Mois" ;
                }
                else if($contrat->getPeriode()->getReference() == "A")
                {
                    $periode = "An(s)" ;
                }
    
                $item["id"] = $contrat->getId() ;
                $item["encodedId"] = $this->encodeChiffre($contrat->getId()) ;
                $item["agence"] = $contrat->getAgence()->getId() ;
                $item["numContrat"] = $contrat->getNumContrat() ;
                $item["dateContrat"] = $contrat->getDateContrat()->format("d/m/Y") ;
                $item["bailleur"] = $contrat->getBailleur()->getNom() ;
                $item["bailleurId"] = $contrat->getBailleur()->getId() ;
                $item["bail"] = $contrat->getBail()->getNom() ;
                $item["bailId"] = $contrat->getBail()->getId() ;
                $item["locataire"] = $contrat->getLocataire()->getNom() ;
                $item["locataireId"] = $contrat->getLocataire()->getId() ;
                $item["cycle"] = $contrat->getCycle()->getNom() ;
                $item["dateDebut"] = $contrat->getDateDebut()->format("d/m/Y") ;
                $item["dateFin"] = $contrat->getDateFin()->format("d/m/Y") ;
                $item["frequence"] = is_null($contrat->getFrequenceRenouv()) ? "Aucun" : $contrat->getFrequenceRenouv()-1;
                $item["dureeContrat"] = $contrat->getDuree()." ".$periode ;
                $item["montantContrat"] = $contrat->getMontantContrat() ;
                $item["statut"] = $contrat->getStatut()->getNom() ;
                $item["refStatut"] = $contrat->getStatut()->getReference() ;
                array_push($items,$item) ;
            }
    
            file_put_contents($params["filename"],json_encode($items)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ;
    }

//    /**
//     * @return LctContrat[] Returns an array of LctContrat objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?LctContrat
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
