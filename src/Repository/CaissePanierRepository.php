<?php

namespace App\Repository;

use App\Entity\CaissePanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaissePanier>
 *
 * @method CaissePanier|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaissePanier|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaissePanier[]    findAll()
 * @method CaissePanier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaissePanierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaissePanier::class);
    }

    public function save(CaissePanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CaissePanier $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getCaissePanier($agence)
    {
        $sql = "
        SELECT date_format(cc.date, '%d/%m/%Y') as date, cc.num_commande as numCommande, 
        p.code_produit as codeProduit, p.nom, cp.quantite, 
        cp.prix, p.id as idP, cp.tva as tva, cc.tva as totalTva , cc.montant_recu as montantRecu, 
        cc.montant_payee as montantPayee, cc.user_id as user, pvp.indice
        FROM `caisse_panier` cp 
        INNER JOIN caisse_commande as cc ON cp.commande_id = cc.id 
        LEFT JOIN prd_histo_entrepot phe ON cp.histo_entrepot_id = phe.id 
        LEFT JOIN prd_variation_prix pvp ON pvp.id = phe.variation_prix_id 
        LEFT JOIN produit p ON pvp.produit_id = p.id 
        WHERE cc.agence_id = ? AND cp.statut= 1 AND cc.statut = 1 
        ORDER BY cc.num_commande ASC; 
        " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$agence]);
        return $resultSet->fetchAllAssociative();
    }

//    /**
//     * @return CaissePanier[] Returns an array of CaissePanier objects
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

//    public function findOneBySomeField($value): ?CaissePanier
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
