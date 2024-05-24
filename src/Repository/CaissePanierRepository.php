<?php

namespace App\Repository;

use App\Entity\CaissePanier;
use App\Entity\PrdHistoEntrepot;
use App\Service\AppService;
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
    private $appService ;

    public function __construct(ManagerRegistry $registry, AppService $appService)
    {
        parent::__construct($registry, CaissePanier::class);

        $this->appService = $appService ;
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

    public function stockTotalCaisseVariationPrix($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as totalCaisseVariation FROM `caisse_panier` WHERE `variation_prix_id` = ? AND `statut` = 1 ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["variationPrix"]]);
        return $resultSet->fetchAssociative();
    }

    public function stockTotalCaisseEntrepot($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT SUM(`quantite`) as totalCaisseEntrepot FROM `caisse_panier` WHERE `histo_entrepot_id` = ? AND `variation_prix_id` = ?  AND `statut` = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["histoEntrepot"],$params["variationPrix"],True]);
        return $resultSet->fetchAssociative();
    }

    public function updateHistoEntrepotCaisse($params = [])
    {
        $isUpdated = $this->appService->verifyIsAgenceUpdated([
            "category" => "maj_histo_entrepot_caisse"
        ]) ;

        if($isUpdated)
            return False ;

        $panierCommandes = $this->getEntityManager()->getRepository(CaissePanier::class)->findBy([
            "agence" => $params["agence"],
            "histoEntrepot" => NULL,
            // "statut" => True,
        ]) ;

        foreach ($panierCommandes as $panierCommande) {
            $histoEntrepot = $this->getEntityManager()->getRepository(PrdHistoEntrepot::class)->findOneBy([
                "variationPrix" => $panierCommande->getVariationPrix(),
                "statut" => True
            ]) ;

            $panierCommande->setHistoEntrepot($histoEntrepot) ;
            $this->getEntityManager()->flush() ;
        }
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
