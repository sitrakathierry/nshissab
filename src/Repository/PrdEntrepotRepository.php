<?php

namespace App\Repository;

use App\Entity\PrdEntrepot;
use App\Entity\PrdHistoEntrepot;
use App\Entity\PrdVariationPrix;
use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdEntrepot>
 *
 * @method PrdEntrepot|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdEntrepot|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdEntrepot[]    findAll()
 * @method PrdEntrepot[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdEntrepotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdEntrepot::class);
    }

    public function save(PrdEntrepot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdEntrepot $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateStockEntrepot($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $entrepots = $this->getEntityManager()->getRepository(PrdEntrepot::class)->findBy([
                "statut" => True,
                "agence" => $params["agence"]
            ]) ;
    
            $elements = [] ;
            
            foreach ($entrepots as $entrepot) {
                $element = [] ;
                $element["id"] = $entrepot->getId() ;
                $element["nom"] = $entrepot->getNom() ;
                $element["adresse"] = $entrepot->getAdresse() ;
                $element["telephone"] = $entrepot->getTelephone() ;
                $element["agence"] = $entrepot->getAgence()->getId() ;
                array_push($elements,$element) ;
            } 
    
            file_put_contents($params["filename"],json_encode($elements)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ;  ;
    }

    public function testHistoEntrepot()
    {
        $entrepots = $this->getEntityManager()->getRepository(PrdEntrepot::class)->findBy([
            "statut" => True
        ]) ;

        foreach ($entrepots as $entrepot) {
            $produits = $this->getEntityManager()->getRepository(Produit::class)->findBy([
                "statut" => True
            ]) ;

            foreach ($produits as $produit) {
                $variationPrixs = $this->getEntityManager()->getRepository(PrdVariationPrix::class)->findBy([
                    "produit" => $produit,
                    "statut" => True
                ]) ;

                foreach ($variationPrixs as $variationPrix) {
                    $histoEntrepots = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findBy([
                        "variationPrix" => $variationPrix,
                        "entrepot" => $entrepot,
                        "statut" => True
                    ]) ;

                    if(count($histoEntrepots) > 1)
                    {
                        dd($histoEntrepots) ;
                    }
                }


            }
        }
    }

//    /**
//     * @return PrdEntrepot[] Returns an array of PrdEntrepot objects
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

//    public function findOneBySomeField($value): ?PrdEntrepot
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
