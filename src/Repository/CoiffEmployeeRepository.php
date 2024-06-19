<?php

namespace App\Repository;

use App\Entity\CoiffEmployee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoiffEmployee>
 *
 * @method CoiffEmployee|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoiffEmployee|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoiffEmployee[]    findAll()
 * @method CoiffEmployee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoiffEmployeeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoiffEmployee::class);
    }

    public function save(CoiffEmployee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoiffEmployee $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generateCoiffEmployee($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $employees = $this->getEntityManager()->getRepository(CoiffEmployee::class)->findBy([
                "agence" => $params["agence"],
                "statut" => True
            ]) ;

            $items = [] ;

            foreach ($employees as $employee) {
                $items[] = [
                    "id" => $employee->getId(),
                    "sexe" => $employee->getSexe()->getNom(),
                    "nom" => $employee->getNom(),
                    "prenom" => $employee->getPrenom(),
                ] ;
            }

            file_put_contents($params["filename"],json_encode($items)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ;
    }

//    /**
//     * @return CoiffEmployee[] Returns an array of CoiffEmployee objects
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

//    public function findOneBySomeField($value): ?CoiffEmployee
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
