<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\CltHistoClient;
use App\Entity\CltSociete;
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

        if($params['telephone'] == "-")
            return False ;

        if($params["typeClient"] == "MORAL")
        {
            $query =  $queryBuilder
                ->select('cs')
                ->from(CltSociete::class, 'cs')
                ->where('cs.tel_fixe LIKE :telephone')
                ->setParameter('telephone', '%' . $params['telephone'] . '%')
                ->getQuery()
                ->getOneOrNullResult(); 
        }
        else
        {
            $query =  $queryBuilder
                ->select('c')
                ->from(Client::class, 'c')
                ->where('c.telephone LIKE :telephone')
                ->setParameter('telephone', '%' . $params['telephone'] . '%')
                ->getQuery()
                ->getOneOrNullResult();
        }

        return !is_null($query) ;
    }

    public function saveClient($params = [])
    {
        if($params["typeClient"]->getReference() == "MORAL")
        {
            $societe = new CltSociete() ;

            $societe->setAgence($params["agence"]) ;
            $societe->setNom($params["nom"]) ;
            $societe->setAdresse($params["adresse"]) ;
            $societe->setTelFixe($params["telephone"]) ;

            $this->getEntityManager()->persist($societe) ;
            $this->getEntityManager()->flush() ;
            $clientP = null ;
        }
        else
        {
            $clientP = new Client() ;

            $clientP->setAgence($params["agence"]) ;
            $clientP->setNom($params["nom"]) ;
            $clientP->setAdresse($params["adresse"]) ;
            $clientP->setTelephone($params["telephone"]) ;
            
            $this->getEntityManager()->persist($clientP) ;
            $this->getEntityManager()->flush() ;
            $societe = null ;
        }

        $client = new CltHistoClient() ;

        $client->setAgence($params["agence"]) ;
        $client->setClient($clientP) ;
        $client->setSociete($societe) ;
        $client->setType($params["typeClient"]) ;
        $client->setUrgence(null) ;
        $client->setStatut(True) ;
        $client->setCreatedAt(new \DateTimeImmutable) ;
        $client->setUpdatedAt(new \DateTimeImmutable) ;

        $this->getEntityManager()->persist($client) ;
        $this->getEntityManager()->flush() ;

        return $client ;
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
