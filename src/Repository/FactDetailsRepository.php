<?php

namespace App\Repository;

use App\Entity\FactDetails;
use App\Entity\FactType;
use App\Entity\Facture;
use App\Entity\PrdApprovisionnement;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdMargeType;
use App\Entity\PrdVariationPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactDetails>
 *
 * @method FactDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactDetails[]    findAll()
 * @method FactDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactDetails::class);
    }

    public function save(FactDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FactDetails $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
 
    public function stockTotalFactureVariation($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        // A CORRIGER 
        $sql = "SELECT SUM(fd.quantite) as totalFactureVariation, pe.id as idEntrepot FROM `fact_details` fd 
                JOIN facture f ON fd.facture_id = f.id
                JOIN prd_entrepot pe ON f.entrepot_id = pe.id 
                WHERE f.agence_id = ? AND f.type_id = ? AND f.ticket_caisse_id IS NULL 
                AND f.statut = ? AND fd.activite = ? AND fd.entite = ? 
                AND fd.statut = ? " ;

        $stmt = $conn->prepare($sql) ;
        $resultSet = $stmt->executeQuery([
            $params["agence"],
            1, 
            1,
            'Produit',
            $params["variationPrix"],
            1
        ]);
        return $resultSet->fetchAssociative(); 
    }

    public function displayFactureVariation($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT DATE_FORMAT(f.date, '%d/%m/%Y') as 'date',fd.designation as produit,fd.quantite as quantite, fd.prix as prix, (fd.prix * fd.quantite) as total , 'Facture Definitif' as type FROM `fact_details` fd
        JOIN facture f ON fd.facture_id = f.id
        WHERE fd.activite = 'Produit' AND fd.statut = 1 AND fd.facture_id = ? AND fd.entite = ? ";
        // $sql = "SELECT fd.designation as produit,fd.quantite as quantite, fd.prix as prix, (fd.prix * fd.quantite) as total , 'Facture Definitif' as 'type' FROM `fact_details` fd
        // WHERE fd.`activite` = 'Produit' AND fd.`statut` = 1 AND fd.`facture_id` = ? AND fd.`entite` = ?";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["facture"],$params["variationPrix"]]);
        return $resultSet->fetchAllAssociative();
    }

    public function findOneByEntite($params = [])
    {
        // $conn = $this->getEntityManager()->getConnection();
  

        // Dans votre méthode de contrôleur ou de service
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fd')
            ->from(FactDetails::class, 'fd')
            ->where('fd.facture = :factureId')
            ->andWhere('fd.activite = :activite')
            ->andWhere('fd.statut = :statut')
            ->andWhere('fd.entite IS NOT NULL')
            ->orderBy('fd.id', 'ASC')
            ->setParameter('factureId', $params['facture']->getId())
            ->setParameter('activite', $params['activite'])
            ->setParameter('statut', $params['statut'])
            ->getQuery() ;
            
            // ->getSingleResult();
        try {
            $result = $query->getOneOrNullResult();
        } catch (\Exception $e) {
            $result = $query->getResult()[0];
        }
        
        return $result ;
    }

    public function findFactCoiffure($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fd')
            ->from(FactDetails::class, 'fd')
            ->join(Facture::class, 'f', 'WITH', 'f.id = fd.facture')
            ->where('f.agence = :agence')
            ->andWhere('fd.statut = :statut')
            ->andWhere('fd.coiffEmployee IS NOT NULL')
            ->setParameter('agence', $params['agence'])
            ->setParameter('statut', $params['statut'])
            ->getQuery() ;

        return $query->getResult() ;
            
    }

    public function findAllByVariation($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fd')
            ->from(FactDetails::class, 'fd')
            ->join(Facture::class, 'f', 'WITH', 'f.id = fd.facture')
            ->join(FactType::class, 'ft', 'WITH', 'f.type = ft.id')
            ->leftJoin(PrdHistoEntrepot::class, 'phe', 'WITH', 'phe.id = fd.histoEntrepot')
            ->where('f.agence = :agence')
            ->andWhere('fd.activite = :activite')
            ->andWhere('fd.statut = :statut')
            // ->andWhere('fd.entite IS NOT NULL')
            ->andWhere('fd.entite = :entite')
            ->andWhere('ft.reference = :referenceType')
            ->andWhere('phe.statut = :statutEnt')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('activite', 'Produit')
            ->setParameter('statutEnt', True)
            ->setParameter('entite', $params['variationPrix']->getId())
            ->setParameter('statut', $params['statut'])
            ->setParameter('referenceType', 'DF')
            ->getQuery() ;
        
        return $query->getResult();
    }

    public function stockTotalFactureInEntrepot($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('SUM(fd.quantite) as totalFactureEntrepot')
            ->from(FactDetails::class, 'fd')
            ->join(Facture::class, 'f', 'WITH', 'fd.facture = f.id')
            ->join(FactType::class, 'ft', 'WITH', 'f.type = ft.id')
            ->where('fd.activite = :activite')
            // ->andWhere('fd.agence = :histoEntrepot')
            ->andWhere('fd.statut = :statut')
            ->andWhere('fd.entite IS NOT NULL')
            ->andWhere('fd.histoEntrepot = :histoEntrepot')
            ->andWhere('ft.reference = :referenceType')
            ->orderBy('fd.id', 'ASC')
            ->setParameter('activite', 'Produit')
            ->setParameter('histoEntrepot', $params["histoEntrepot"] )
            ->setParameter('statut', True)
            ->setParameter('referenceType', 'DF')
            ->getQuery() ;

        // $result = $query->getSingleScalarResult();

        // foreach ($facDetails as $facDetail) {
        //     $variationPrix = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($facDetail->getEntite()) ;

        //     $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
        //         "entrepot" => $facDetail->getFacture()->getEntrepot(),
        //         "variationPrix" => $variationPrix,
        //         "statut" => True
        //     ],["id" => "ASC"]) ;

        //     if(!is_null($histoEntrepot))
        //     {
        //         if($histoEntrepot->getId() == $params["histoEntrepot"])
        //         {
        //             $result += $facDetail->getQuantite() ;
        //         }
        //     }
        // }

        return $query->getSingleScalarResult() ;
    }

    public function updateFactDetailsHistoEntrepot($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fd')
            ->from(FactDetails::class, 'fd')
            ->join(Facture::class, 'f', 'WITH', 'f.id = fd.facture')
            ->where('fd.activite = :activite')
            ->andWhere('f.agence = :agence')
            ->andWhere('fd.statut = :statut')
            ->andWhere('fd.entite IS NOT NULL')
            ->andWhere('fd.histoEntrepot IS NULL')
            ->orderBy('fd.id', 'ASC')
            ->setParameter('activite', 'Produit')
            ->setParameter('agence', $params["agence"]->getId())
            ->setParameter('statut', True)
            ->getQuery() ;

        $facDetails = $query->getResult();

        foreach ($facDetails as $facDetail) {
            $histoEntrepot = $this->findHistoEntrepotInFacture([
                "factDetail" => $facDetail,
                "agence" => $params["agence"],
                "user" => $params["user"],
            ]) ;

            $facDetail->setHistoEntrepot($histoEntrepot) ;
            $this->getEntityManager()->flush() ;
        }
    }

    public function findHistoEntrepotInFacture($params = [])
    {
        if(isset($params["variationPrix"]))
        {
            $variationPrix = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->find($params["variationPrix"]) ;
            $entrepot = $params["entrepot"] ; 
        }
        else
        {
            $variationPrix = $params["factDetail"]->getEntite() ;
            $entrepot = $params["factDetail"]->getFacture()->getEntrepot() ;
        }

        if(is_null($entrepot))
            return NULL ;

        $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
            "entrepot" => $entrepot,
            "variationPrix" => $variationPrix,
            "statut" => True
        ],["id" => "ASC"]) ;

        if(is_null($histoEntrepot))
        {
            $histoEntrepot = new PrdHistoEntrepot() ;

            $histoEntrepot->setEntrepot($entrepot) ;
            $histoEntrepot->setVariationPrix($variationPrix) ;
            $histoEntrepot->setStock(1) ;
            $histoEntrepot->setStatut(True) ;
            $histoEntrepot->setAgence($params["agence"]) ;
            $histoEntrepot->setAnneeData(date('Y')) ;
            $histoEntrepot->setCreatedAt(new \DateTimeImmutable) ;
            $histoEntrepot->setUpdatedAt(new \DateTimeImmutable) ;

            $this->getEntityManager()->persist($histoEntrepot) ;
            $this->getEntityManager()->flush() ;

            $approvisionnement = new PrdApprovisionnement() ;

            $margeType = $this->getEntityManager()->getRepository(PrdMargeType::class)->find(1) ; // Par défaut Montant

            $approvisionnement->setAgence($params["agence"]) ;
            $approvisionnement->setUser($params["user"]) ;
            $approvisionnement->setHistoEntrepot($histoEntrepot) ;
            $approvisionnement->setVariationPrix($variationPrix) ;
            $approvisionnement->setMargeType($margeType) ;
            $approvisionnement->setQuantite($params["factDetail"]->getQuantite()) ;
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

        return $histoEntrepot ;
    }

    public function findAllByAgence($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fd')
            ->from(FactDetails::class, 'fd')
            ->join(Facture::class, 'f', 'WITH', 'f.id = fd.facture')
            ->leftJoin(FactType::class, 'ft', 'WITH', 'ft.id = f.type')
            ->where('f.agence = :agence')
            ->andWhere('fd.statut = :statut')
            ->andWhere('ft.reference = :type')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('statut', $params['statut'])
            ->setParameter('type', $params['type'])
            ->getQuery() ;
        
        return $query->getResult();
    }

//    /**
//     * @return FactDetails[] Returns an array of FactDetails objects
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

//    public function findOneBySomeField($value): ?FactDetails
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
