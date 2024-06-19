<?php

namespace App\Repository;

use App\Entity\CoiffCpPrix;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CoiffCpPrix>
 *
 * @method CoiffCpPrix|null find($id, $lockMode = null, $lockVersion = null)
 * @method CoiffCpPrix|null findOneBy(array $criteria, array $orderBy = null)
 * @method CoiffCpPrix[]    findAll()
 * @method CoiffCpPrix[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoiffCpPrixRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CoiffCpPrix::class);
    }

    public function save(CoiffCpPrix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CoiffCpPrix $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function generatePrixCoupes($params = [])
    {
        if(!file_exists($params["filename"]))
        {
            $cpPrixs = $this->getEntityManager()->getRepository(CoiffCpPrix::class)->findBy([
                "agence" => $params["agence"],
                "statut" => True
            ]) ;

            $items = [] ;

            foreach ($cpPrixs as $cpPrix) {
                $categorie = $cpPrix->getCoupes()->getCategorie() ;
                $coupe = $cpPrix->getCoupes() ;
                $items[] = [
                    "id" => $cpPrix->getId(),
                    "genre" => $categorie->getGenre(),
                    "categorie" => $categorie->getGenre()." | ".$categorie->getNom(),
                    "photo" => $coupe->getPhoto(),
                    "nom" => $coupe->getNom(),
                    "prix" => $cpPrix->getMontant(),
                ] ;
            }

            file_put_contents($params["filename"],json_encode($items)) ;
        }

        return json_decode(file_get_contents($params["filename"])) ; 
    }

//    /**
//     * @return CoiffCpPrix[] Returns an array of CoiffCpPrix objects
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

//    public function findOneBySomeField($value): ?CoiffCpPrix
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
