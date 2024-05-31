<?php

namespace App\Repository;

use App\Entity\CltHistoClient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CltHistoClient>
 *
 * @method CltHistoClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method CltHistoClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method CltHistoClient[]    findAll()
 * @method CltHistoClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CltHistoClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CltHistoClient::class);
    }

    public function save(CltHistoClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CltHistoClient $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function verifyPhoneClient($params = [])
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();

        if($params["typeClient"] == "MORAL")
        {
            $query =  $queryBuilder
                ->select('c')
                ->from(CltSociete::class, 'chc')
                ->where('c.telFixe LIKE "%:telephone%" ')
                ->setParameter('telephone', $params['telephone'])
                ->getQuery() 
                ->getOneOrNullResult();
        }
        else
        {
            $query =  $queryBuilder
                ->select('cs')
                ->from(Client::class, 'cs')
                ->where('cs.telephone LIKE "%:telephone%" ')
                ->setParameter('telephone', $params['telephone'])
                ->getQuery() 
                ->getOneOrNullResult();
        }

        return !is_null($query) ;
    }

//    /**
//     * @return CltHistoClient[] Returns an array of CltHistoClient objects
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
//          ;
//    }

//    public function findOneBySomeField($value): ?CltHistoClient
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
