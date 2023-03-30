<?php

namespace App\Repository;

use App\Entity\MenuUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MenuUser>
 *
 * @method MenuUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuUser[]    findAll()
 * @method MenuUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MenuUser::class);
    }

    public function save(MenuUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(MenuUser $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function allMenu($parent, $user)
    {
        $conn = $this->getEntityManager()->getConnection();

        if(is_null($parent))
            $menuParent = " m.menu_parent_id IS NULL" ;
        else
            $menuParent = "m.menu_parent_id = ? " ;

        $sql = "
                SELECT
                m.menu_parent_id as parent, 
                m.id,
                IF(m.route IS NULL,'none',m.route) as route, 
                m.nom, m.icone, m.rang
                FROM `menu_user` mu 
                JOIN menu_agence ma ON mu.menu_agence_id = ma.id 
                LEFT JOIN menu m ON ma.menu_id = m.id 
                WHERE $menuParent
                AND mu.user_id = ? AND m.statut = 1 
                AND ma.statut = 1 AND mu.statut = 1
                ORDER BY m.rang ASC; 
            ";
        $stmt = $conn->prepare($sql);
        if(is_null($parent))
            $resultSet = $stmt->executeQuery([$user]);
        else
            $resultSet = $stmt->executeQuery([$parent,$user]);
        
        return $resultSet->fetchAllAssociative();
    }


//    /**
//     * @return MenuUser[] Returns an array of MenuUser objects
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

//    public function findOneBySomeField($value): ?MenuUser
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
