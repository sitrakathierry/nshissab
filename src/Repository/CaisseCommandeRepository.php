<?php

namespace App\Repository;

use App\Entity\CaisseCommande;
use App\Entity\CaissePanier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaisseCommande>
 *
 * @method CaisseCommande|null find($id, $lockMode = null, $lockVersion = null)
 * @method CaisseCommande|null findOneBy(array $criteria, array $orderBy = null)
 * @method CaisseCommande[]    findAll()
 * @method CaisseCommande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaisseCommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaisseCommande::class);
    }

    public function save(CaisseCommande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CaisseCommande $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateRecetteCaisse($params = [])
    {
        $commandes = $this->getEntityManager()->getRepository(CaisseCommande::class)->findBy([
            "agence" => $params["agence"],
            "statut" => $params["statut"]
        ],["date" => "DESC"]) ;
        
        $elements = [] ;

        foreach ($commandes as $commande) {
            $caissePanier = $this->getEntityManager()->getRepository(CaissePanier::class)->findOneBy([
                "commande" => $commande,
                "statut" => True,
            ]) ;

            if($commande->getMontantPayee() > 0)
            {
                $entrepot = "-" ;
                $refEntrepot = "-" ;

                
                if(!is_null($caissePanier) && !is_null($caissePanier->getHistoEntrepot()))
                {
                    $entrepot = $caissePanier->getHistoEntrepot()->getEntrepot()->getNom() ;
                    $refEntrepot = $caissePanier->getHistoEntrepot()->getEntrepot()->getId() ;
                }

                $elements[] = [
                    "id" => $commande->getId(),
                    "date" => $commande->getDate()->format('d/m/Y'),
                    "currentDate" => $commande->getDate()->format('d/m/Y'),
                    "dateFacture" => $commande->getDate()->format('d/m/Y'),
                    "dateDebut" => $commande->getDate()->format('d/m/Y'),
                    "dateFin" => $commande->getDate()->format('d/m/Y'),
                    "annee" => $commande->getDate()->format('Y'),
                    "mois" => $commande->getDate()->format('m'),
                    "numero" => $commande->getNumCommande(),
                    "entrepot" => $entrepot,
                    "refEntrepot" => $refEntrepot,
                    "montant" => $commande->getMontantPayee(),
                    "typePaiement" => "-",
                    "refTypePaiement" => "-",
                    "recette" => "Caisse",
                    "refRecette" => "CAISSE",
                ] ;
            }
        }

        return $elements ;
    }
//    /**
//     * @return CaisseCommande[] Returns an array of CaisseCommande objects
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

//    public function findOneBySomeField($value): ?CaisseCommande
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
