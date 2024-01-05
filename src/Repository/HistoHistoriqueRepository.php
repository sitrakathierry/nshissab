<?php

namespace App\Repository;

use App\Entity\HistoAction;
use App\Entity\HistoHistorique;
use App\Entity\HistoModule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoHistorique>
 *
 * @method HistoHistorique|null find($id, $lockMode = null, $lockVersion = null)
 * @method HistoHistorique|null findOneBy(array $criteria, array $orderBy = null)
 * @method HistoHistorique[]    findAll()
 * @method HistoHistorique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HistoHistoriqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoHistorique::class);
    }

    public function save(HistoHistorique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(HistoHistorique $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function insererHistorique($params = [])
    {
        $module = $this->getEntityManager()->getRepository(HistoModule::class)->findOneBy([
            "reference" => $params["refModule"],
            "statut" => True
        ]) ;

        if(is_null($module))
        {
            $module = new HistoModule() ;

            $module->setNom($params["nomModule"]) ;
            $module->setReference($params["refModule"]) ;
            $module->setStatut(True) ;

            $this->getEntityManager()->persist($module);
            $this->getEntityManager()->flush();
        }

        $action = $this->getEntityManager()->getRepository(HistoAction::class)->findOneBy([
            "reference" => $params["refAction"],
            "statut" => True
        ]) ;

        $historique = new HistoHistorique() ;

        $historique->setAction($action) ;
        $historique->setModule($module) ;
        $historique->setUser($params["user"]) ;
        $historique->setAgence4($params["agence"]) ;
        $historique->setDateHeure(new \DateTime) ;
        $historique->setDescription($params["description"]) ;
        $historique->setStatut(True) ;
        $historique->setCreatedAt(new \DateTimeImmutable) ;
        $historique->setUpdatedAt(new \DateTimeImmutable) ;

        $this->getEntityManager()->persist($historique);
        $this->getEntityManager()->flush();

        $filename = "files/systeme/historique/".$params["nameAgence"] ;
        if(file_exists($filename))
            unlink($filename) ;

    }

    public function genererHistorique($agence,$filename)
    {
        $historiques = $this->getEntityManager()->getRepository(HistoHistorique::class)->findBy([
            "agence4" => $agence,
            "statut" => True
        ]) ;

        $items = [] ;

        foreach ($historiques as $historique) {
            $item = [
                "date" => $historique->getDateHeure()->format("d/m/Y"),
                "heure" => $historique->getDateHeure()->format("H:i"),
                "action" => $historique->getAction()->getNom(),
                "utilisateur" => strtoupper($historique->getUser()->getUsername()),
                "module" => $historique->getModule()->getNom(),
                "description" => $historique->getDescription()
            ] ;

            array_push($items,$item) ;
        }

        file_put_contents($filename,json_encode($items)) ;
    }

//    /**
//     * @return HistoHistorique[] Returns an array of HistoHistorique objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('h.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?HistoHistorique
//    {
//        return $this->createQueryBuilder('h')
//            ->andWhere('h.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
