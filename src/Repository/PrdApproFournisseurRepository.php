<?php

namespace App\Repository;

use App\Entity\PrdApproFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrdApproFournisseur>
 *
 * @method PrdApproFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrdApproFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrdApproFournisseur[]    findAll()
 * @method PrdApproFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrdApproFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrdApproFournisseur::class);
    }

    public function save(PrdApproFournisseur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PrdApproFournisseur $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PrdApproFournisseur[] Returns an array of PrdApproFournisseur objects
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

//    public function findOneBySomeField($value): ?PrdApproFournisseur
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
