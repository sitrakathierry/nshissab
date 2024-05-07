<?php

namespace App\Repository;

use App\Entity\PrdApprovisionnement;
use App\Service\AppService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdApprovisionnement>
 *
 * @method PrdApprovisionnement|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdApprovisionnement|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdApprovisionnement[]    findAll()
 * @method PrdApprovisionnement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdApprovisionnementRepository extends ServiceEntityRepository
{
    private $appService ;

    public function __construct(ManagerRegistry $registry, AppService $appService)
    {
        parent::__construct($registry, PrdApprovisionnement::class);

        $this->appService = $appService ;
    }

    public function save(PrdApprovisionnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdApprovisionnement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getLastApproVariationPrix($idVar)
    {
        $repository = $this->getEntityManager()->getRepository(PrdApprovisionnement::class) ; 

        $query = $repository->createQueryBuilder('e')
            ->where('e.variationPrix = :variation')
            ->andWhere('e.prixAchat IS NOT NULL')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults(1)
            ->setParameter('variation', $idVar)
            ->getQuery();
        return $query->getOneOrNullResult();
    }

    public function stockTotalVariationPrix($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as stockTotalVariation FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? ";
        // $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? AND `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["variationPrix"]]);
        // $resultSet = $stmt->executeQuery([$params["variationPrix"],$params["histoEntrepot"]]);
        return $resultSet->fetchAssociative();
    }

    public function stockTotalHistoEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(pa.quantite) as stockTotalEntrepot FROM `prd_approvisionnement` pa 
            JOIN prd_histo_entrepot phe ON phe.id = pa.histo_entrepot_id  
            WHERE `histo_entrepot_id` = ? AND phe.statut = ? ";
        // $sql = "SELECT SUM(`quantite`) as stockTotalEntrepot FROM `prd_approvisionnement` WHERE `variation_prix_id` = ? AND `histo_entrepot_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["histoEntrepot"],
            True,
        ]);
        // $resultSet = $stmt->executeQuery([$params["variationPrix"],$params["histoEntrepot"]]);
        return $resultSet->fetchAssociative();
    }

    public function generateProduitExpiree($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $filename = "files/systeme/stock/approvisionnement(agence)/".$params["nameAgence"] ;
            
            if(!file_exists($filename))
                $this->appService->generatePrdListeApprovisionnement($filename, $params["agence"]) ;

    
            $appros = json_decode(file_get_contents($filename)) ;
    
            $groupedData = [];
    
            foreach ($appros as $item) {
                $key = $item->dateExpiration . '-' . $item->indice . '-' . $item->prixVente;
    
                if (!isset($groupedData[$key])) {
                    $groupedData[$key] = [];
                    $groupedData[$key]["prixVente"] = $item->prixVente ;
                    $groupedData[$key]["dateExpiration"] = $item->dateExpiration ;
                    $groupedData[$key]["nomProduit"] = $item->nomProduit ;
                    $groupedData[$key]["codeProduit"] = $item->codeProduit ;
                    $groupedData[$key]["nomType"] = $item->nomType ;
                    $groupedData[$key]["indice"] = $item->indice ;
                    $groupedData[$key]["variation"] = $item->variation ;
                    $groupedData[$key]["stock"] = 0 ;
                }
                $groupedData[$key]["stock"] += $item->stock ;
            }
    
            $produitExpirees = [] ;
    
            foreach ($groupedData as $produitExpiree) {
                if($produitExpiree["dateExpiration"] == "-")
                    continue ;
                
                $compareDate = $this->appService->compareDates($produitExpiree["dateExpiration"],date("d/m/Y"),'P') ;
                if($compareDate)
                {
                    $produitExpirees[] = $produitExpiree ;
                }
            }

            file_put_contents($params["filename"],json_encode($produitExpirees)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ;
    }

//    /**
//     * @return PrdApprovisionnement[] Returns an array of PrdApprovisionnement objects
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

//    public function findOneBySomeField($value): ?PrdApprovisionnement
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
