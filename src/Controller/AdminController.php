<?php

namespace App\Controller;

use App\Entity\MenuUser;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class AdminController extends AbstractController
{
    private $entityManager;
    private $session ;
    private $appService ;
    public function __construct(EntityManagerInterface $entityManager,SessionInterface $session, AppService $appService)
    {
        $this->session = $session;
        $this->entityManager = $entityManager;
        $this->appService = $appService ;
    }

    #[Route('/admin', name: 'app_admin')]
    public function index(): Response
    {
        $user = $this->session->get("user")  ; 
        $filename = "files/json/menu/".$user['username'].".json" ;
        if(!file_exists($filename))
        {
            $userClass = $this->entityManager
                            ->getRepository(User::class)
                            ->findOneBy(array("email" => $user['email'])) ;
            
            $menus = [] ;
            $menuUsers = $this->entityManager
                            ->getRepository(MenuUser::class)
                            ->allMenu(null, $userClass->getId()) ;
            $id = 0;
            $this->appService->getMenu($menuUsers,$id,$menus) ;
            
            $json = json_encode($menus) ;
            file_put_contents($filename, $json); 
        }
        
        return $this->render('admin/index.html.twig');
    }
}
