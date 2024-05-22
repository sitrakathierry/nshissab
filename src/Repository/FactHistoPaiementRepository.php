<?php

namespace App\Repository;

use App\Entity\FactDetails;
use App\Entity\FactHistoPaiement;
use App\Entity\FactType;
use App\Entity\Facture;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactHistoPaiement>
 *
 * @method FactHistoPaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactHistoPaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactHistoPaiement[]    findAll()
 * @method FactHistoPaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactHistoPaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactHistoPaiement::class);
    }

    public function save(FactHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FactHistoPaiement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateMontantFactureDef($params = [])
    {   
        $factures = $this->getEntityManager()->getRepository(Facture::class)->findByAgence([
            "agence" => $params["agence"],
            "statut" => True,
            "type" => 'DF',
        ]) ;

        foreach ($factures as $facture) {
            
            $histoPaiements = $this->getEntityManager()->getRepository(FactHistoPaiement::class)->findBy([
                "facture" => $facture
            ]) ;

            if(count($histoPaiements) == 1)
            {
                if($histoPaiements[0]->getPaiement()->getReference() == "CR")
                    continue ;

                $factDetails = $this->getEntityManager()->getRepository(FactDetails::class)->findBy([
                    "facture" => $facture,
                    "statut" => True,
                ]) ;
                   
                $totalHt = 0 ;
                $totalTva = 0 ;

                foreach ($factDetails as $factDetail) {
                    $tvaVal = is_null($factDetail->getTvaVal()) ? 0 : $factDetail->getTvaVal() ;
                    $tva = (($factDetail->getPrix() * $tvaVal) / 100) * $factDetail->getQuantite();
                    $total = $factDetail->getPrix() * $factDetail->getQuantite()  ;
                    $remise = $params["appService"]->getFactureRemise($factDetail,$total) ; 
                    
                    $total = $total - $remise ;

                    $totalHt += $total ;
                    $totalTva += $tva ;
                }
                
                $remiseGen = $params["appService"]->getFactureRemise($facture,$totalHt) ;
                $totalTtc = $totalHt + $totalTva - $remiseGen ;
     
                $histoPaiements[0]->setMontant($totalTtc) ;
                $this->getEntityManager()->flush() ;
            }
        }
    }

    public function findHistoPActive($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        $query = $queryBuilder
            ->select('fhp')
            ->from(FactHistoPaiement::class, 'fhp')
            ->join(Facture::class, 'f', 'WITH', 'f.id = fhp.facture')
            ->leftJoin(FactType::class, 'ft', 'WITH', 'ft.id = f.type')
            ->where('f.agence = :agence')
            ->andWhere('f.statut = :statut')
            ->andWhere('ft.reference = :type')
            ->setParameter('agence', $params['agence']->getId())
            ->setParameter('statut', $params['statut'])
            ->setParameter('type', $params['type'])
            ->getQuery() ;
        
        return $query->getResult();
    }

//    /**
//     * @return FactHistoPaiement[] Returns an array of FactHistoPaiement objects
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

//    public function findOneBySomeField($value): ?FactHistoPaiement
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
