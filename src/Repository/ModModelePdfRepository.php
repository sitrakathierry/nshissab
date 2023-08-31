<?php

namespace App\Repository;

use App\Entity\ModModelePdf;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModModelePdf>
 *
 * @method ModModelePdf|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModModelePdf|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModModelePdf[]    findAll()
 * @method ModModelePdf[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModModelePdfRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModModelePdf::class);
    }

    public function save(ModModelePdf $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ModModelePdf $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ModModelePdf[] Returns an array of ModModelePdf objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ModModelePdf
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
