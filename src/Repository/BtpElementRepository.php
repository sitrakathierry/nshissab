<?php

namespace App\Repository;

use App\Entity\BtpElement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BtpElement>
 *
 * @method BtpElement|null find($id, $lockMode = null, $lockVersion = null)
 * @method BtpElement|null findOneBy(array $criteria, array $orderBy = null)
 * @method BtpElement[]    findAll()
 * @method BtpElement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BtpElementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BtpElement::class);
    }

    public function save(BtpElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BtpElement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getInformationElement($params = [])
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT be.nom as designation, IF(bm.notation IS NULL,'-', bm.notation) as mesure FROM `fact_details` fd JOIN btp_element be ON be.id = fd.entite JOIN btp_mesure bm ON bm.id = be.mesure_id WHERE fd.id = ? ";
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery([$params["idFactDetail"]]);
        return $resultSet->fetchAssociative();
    }
//    /**
//     * @return BtpElement[] Returns an array of BtpElement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BtpElement
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
