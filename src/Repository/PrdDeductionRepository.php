<?php

namespace App\Repository;

use App\Entity\PrdDeduction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdDeduction>
 *
 * @method PrdDeduction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdDeduction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdDeduction[]    findAll()
 * @method PrdDeduction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdDeductionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdDeduction::class);
    }

    public function save(PrdDeduction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdDeduction $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getSommeDeductionEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as sommeStock FROM `prd_deduction` WHERE `histo_entrepot_id` = ? AND `variation_prix_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["histoEntrepot"], $params["variationPrix"]]);
        return $resultSet->fetchAssociative();
    }

    public function getSommeDeductionVariation($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as sommeVariation FROM `prd_deduction` WHERE `variation_prix_id` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["variationPrix"]]);
        return $resultSet->fetchAssociative();
    }

    public function generateProduiDeduit($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $elements = [] ;
            
            $deductionProduits = $this->getEntityManager()->getRepository(PrdDeduction::class)->findBy([
                "agence" => $params["agence"],
            ],["created_at" => "DESC"]) ;
            
            foreach ($deductionProduits as $deductionProduit) {
                $nomProduit = is_null($deductionProduit->getVariationPrix()->getProduit()->getType()) ? "NA" :$deductionProduit->getVariationPrix()->getProduit()->getType()->getNom() ;
                $elements[] = [
                    "id" => $deductionProduit->getId() ,
                    "date" => $deductionProduit->getCreatedAt()->format("d/m/Y") ,
                    "entrepot" => $deductionProduit->getHistoEntrepot()->getEntrepot()->getNom() ,
                    "nomProduit" => $nomProduit ,
                    "designation" => $deductionProduit->getVariationPrix()->getProduit()->getNom() ,
                    "codeProduit" => $deductionProduit->getVariationPrix()->getProduit()->getCodeProduit() ,
                    "indice" => is_null($deductionProduit->getVariationPrix()->getIndice()) ? "-" : $deductionProduit->getVariationPrix()->getIndice()  ,
                    "quantite" => $deductionProduit->getQuantite() ,
                ] ;
            }

            file_put_contents($params["filename"],json_encode($elements)) ;
        }   

        return json_decode(file_get_contents($params["filename"])) ;
    }

    public function getAssocDepotInDeduction($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $response = $queryBuilder
                ->select('pd')
                ->from(PrdDeduction::class, 'pd')
                ->where('pd.cause LIKE :cause')
                ->andWhere('pd.variationPrix = :variation_prix')
                ->andWhere('DATE(pd.createdAt) = :createdAt')
                ->andWhere('pd.quantite = :quantite')
                // ->orderBy('p.id', "DESC") 
                ->setMaxResults(1) 
                ->setParameter('cause','%Déduction sur Dépôt Dépot%')
                ->setParameter('variation_prix',$params["variationPrix"])
                ->setParameter('createdAt',$params["createdAt"]->format("Y-m-d"))
                ->setParameter('quantite',$params["quantite"])
                ->getQuery()
                ->getResult() ;

        return $response[0] ;
    }
//    /**
//     * @return PrdDeduction[] Returns an array of PrdDeduction objects
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

//    public function findOneBySomeField($value): ?PrdDeduction
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
