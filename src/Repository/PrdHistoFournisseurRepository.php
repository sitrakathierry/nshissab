<?php

namespace App\Repository;

use App\Entity\PrdHistoFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdHistoFournisseur>
 *
 * @method PrdHistoFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdHistoFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdHistoFournisseur[]    findAll()
 * @method PrdHistoFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdHistoFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdHistoFournisseur::class);
    }

    public function save(PrdHistoFournisseur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdHistoFournisseur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PrdHistoFournisseur[] Returns an array of PrdHistoFournisseur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PrdHistoFournisseur
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
