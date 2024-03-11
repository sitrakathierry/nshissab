<?php

namespace App\Repository;

use App\Entity\CltHistoClient;
use App\Entity\SavAvoirUse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SavAvoirUse>
 *
 * @method SavAvoirUse|null find($id, $lockMode = null, $lockVersion = null)
 * @method SavAvoirUse|null findOneBy(array $criteria, array $orderBy = null)
 * @method SavAvoirUse[]    findAll()
 * @method SavAvoirUse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SavAvoirUseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SavAvoirUse::class);
    }

//    /**
//     * @return SavAvoirUse[] Returns an array of SavAvoirUse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SavAvoirUse
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function getSommeAvoirUse($params = [])
    {
        $newAvoir = $this->getEntityManager()->getRepository(SavAvoirUse::class)->findOneBy($params);

        if(is_null($newAvoir))
            return $newAvoir ;

        $response = $this->createQueryBuilder('au')
            ->select('SUM(au.montant) as sommeAvoirUse')
            ->andWhere('au.histoClient = :histoClient')
            ->setParameter('histoClient', $params["histoClient"])
            ->getQuery()
            ->getSingleScalarResult();
        
        $allAvoirs = $this->getEntityManager()->getRepository(SavAvoirUse::class)->findBy($params);
        
        foreach ($allAvoirs as $allAvoir) {
            $allAvoir->setIsNew(null) ;
            $this->getEntityManager()->flush() ;
        }

        return $response ;
    }
}
