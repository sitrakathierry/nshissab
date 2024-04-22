<?php

namespace App\Repository;

use App\Entity\FactDetails;
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
        $sql = "SELECT SUM(fd.quantite) as totalFactureVariation FROM `fact_details` fd 
                JOIN facture f ON fd.facture_id = f.id 
                WHERE f.agence_id = ? AND f.type_id = ? AND f.ticket_caisse_id IS NULL 
                AND f.statut = ? AND fd.activite = ? AND fd.entite = ? AND fd.statut = ? ";

        // $sql = "SELECT SUM(`quantite`) as totalFactureVariation FROM `fact_details` WHERE `activite` = 'Produit' AND `statut` = 1 AND `facture_id` = ? AND `entite` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([
            $params["agence"],
            1,
            1,
            'Produit',
            // $params["facture"],
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
