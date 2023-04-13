<?php

namespace App\Repository;

use App\Entity\MenuUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

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
    private $session ;
    public function __construct(ManagerRegistry $registry, SessionInterface $session)
    {
        parent::__construct($registry, MenuUser::class) ;
        $this->session = $session ;
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
            $menuParent = " m.menu_parent_id IS NULL " ;
        else
            $menuParent = "m.menu_parent_id = ? " ;

        $roleUser = $this->session->get("user")["role"] ;
        if($roleUser == "ADMIN")
        {
            $req = " AND m.is_admin = 1 " ;
        }
        else
        {
            $req = " AND m.is_admin IS NULL " ;
        }

        $sql = "SELECT
                m.menu_parent_id as parent, 
                m.id,
                IF(m.route IS NULL,IF(FIND_IN_SET('ADMIN', u.roles) > 0,'app_admin','app_home'),m.route) as route, 
                m.nom, m.icone, m.rang
                FROM `menu_user` mu 
                JOIN menu_agence ma ON mu.menu_agence_id = ma.id 
                LEFT JOIN menu m ON ma.menu_id = m.id 
                RIGHT JOIN user u ON u.id = mu.user_id
                WHERE $menuParent
                AND mu.user_id = ? AND m.statut = 1 
                AND ma.statut = 1 AND mu.statut = 1
                $req
                ORDER BY m.rang ASC; 
            ";
        $stmt = $conn->prepare($sql);
        if(is_null($parent))
            $resultSet = $stmt->executeQuery([$user]);
        else
            $resultSet = $stmt->executeQuery([$parent,$user]);
        return $resultSet->fetchAllAssociative();
    }

    public function allMenuAgence($parent, $agence)
    {
        $conn = $this->getEntityManager()->getConnection();

        if(is_null($parent))
            $menuParent = " m.menu_parent_id IS NULL" ;
        else
            $menuParent = "m.menu_parent_id = ? " ;
        $sql = "SELECT
                m.menu_parent_id as parent, 
                m.id,
                IF(m.route IS NULL,'app_home',m.route) as route, 
                m.nom, m.icone, m.rang
                FROM menu_agence ma
                JOIN menu m ON ma.menu_id = m.id 
                WHERE $menuParent
                AND ma.agence_id = ? 
                AND m.statut = 1 
                AND ma.statut = 1
                AND m.is_admin IS NULL
                ORDER BY m.rang ASC ";
        $stmt = $conn->prepare($sql);
        if(is_null($parent))
            $resultSet = $stmt->executeQuery([$agence]);
        else
            $resultSet = $stmt->executeQuery([$parent,$agence]);
        
        return $resultSet->fetchAllAssociative();
    }

    public function allMenuUser($parent = null)
    {
        $sql = " SELECT `id`,IF(`route` IS NULL,'app_admin',`route`) as route,`nom`,`icone` FROM `menu` WHERE `menu_parent_id` IS NULL AND `statut` = 1 AND is_admin IS NULL ORDER BY `rang` ASC" ;
        if(!is_null($parent))  
            $sql = " SELECT `id`,IF(`route` IS NULL,'app_admin',`route`) as route,`nom`,`icone` FROM `menu` WHERE `statut` = 1 AND is_admin IS NULL AND `menu_parent_id` = ? ORDER BY `rang` ASC " ;
        $conn = $this->getEntityManager()->getConnection();
        $stmt = $conn->prepare($sql);
        
        if(!is_null($parent))  
            $resultSet = $stmt->executeQuery([$parent]);
        else
            $resultSet = $stmt->executeQuery([]);

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
