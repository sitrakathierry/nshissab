<?php

namespace App\Repository;

use App\Entity\PrdType;
use App\Entity\Produit;
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
            $stockGenerales = $this->entityManager->getRepository(Produit::class)->findBy([
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
