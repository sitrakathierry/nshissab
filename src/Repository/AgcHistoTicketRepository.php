<?php

namespace App\Repository;

use App\Entity\AgcHistoTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AgcHistoTicket>
 *
 * @method AgcHistoTicket|null find($id, $lockMode = null, $lockVersion = null)
 * @method AgcHistoTicket|null findOneBy(array $criteria, array $orderBy = null)
 * @method AgcHistoTicket[]    findAll()
 * @method AgcHistoTicket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgcHistoTicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AgcHistoTicket::class);
    }

    public function save(AgcHistoTicket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(AgcHistoTicket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return AgcHistoTicket[] Returns an array of AgcHistoTicket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AgcHistoTicket
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
