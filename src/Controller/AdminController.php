<?php

namespace App\Controller;

use App\Entity\Agence;
use App\Entity\MenuUser;
use App\Entity\User;
use App\Service\AppService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }

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

    #[Route('/admin/societe/add', name: 'admin_add_societe')]
    public function addSociete()
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }

        $password = $this->appService->generatePassword() ;
        return $this->render('admin/societe/add.html.twig',[
            'password' => $password
        ]);
    }

    #[Route('/admin/societe/save', name:'admin_saveSociete')]
    public function saveSociete(Request $request)
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }

        $nom = $request->request->get('nom') ;
        $region = $request->request->get('region') ;
        $capacite = $request->request->get('capacite') ;
        $adresse = $request->request->get('adresse') ;
        $telephone = $request->request->get('telephone') ;

        $username = $request->request->get('username') ;
        $password = $request->request->get('password') ;
        $email = $request->request->get('email') ;
        $poste = $request->request->get('poste') ;

        $data = [$nom, $region, $capacite, $adresse, $telephone,$username ,$password ,$email ,$poste ] ;
        $dataMessage = ["nom", "region", "nombre de compte", "adresse", "telephone","nom d'utilisateur" ,"mot de passe" ,"email" ,"responsabilite" ] ;
        $allow = True ;
        $message = "Information enregistré avec succès" ;
        $type = "green" ;
        for ($i=0; $i < count($data); $i++) { 
            if($i != 2)
            {
                if(empty($data[$i]))
                {
                    $allow = False ;
                    $type="orange" ;
                    $message = $dataMessage[$i]." vide" ;
                    break;
                }
            }
            else
            {
                if(empty($data[$i]))
                {
                    $allow = False ;
                    $type="orange" ;
                    $message = $dataMessage[$i]." vide" ;
                    break;
                }
                else if(intval($data[$i]) <= 0)
                {
                    $allow = False ;
                    $type="red" ;
                    $message = $dataMessage[$i]." doit être supérieur à 0" ;
                    break;
                }
            }
        } 

        $chk_uname = $this->entityManager->getRepository(User::class)->findOneBy(["username" => strtoupper($username)]) ;

        if(!is_null($chk_uname))
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre nom d'utilisateur existe déjà, veuillez entrer un autre" ;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $chk_email = $this->entityManager->getRepository(User::class)->findOneBy(["email" => $email]) ;
            if(!is_null($chk_email))
            {
                $allow = False ;
                $type="orange" ;
                $message = "Votre adresse email existe déjà, veuillez entrer un autre" ;
            }
        } else {
            $allow = False ;
            $type="red" ;
            $message = "Votre adresse email est invalide" ;
        }
        
        if(strlen($password) < 8)
        {
            $allow = False ;
            $type="orange" ;
            $message = "Votre mot de passe doit contenir au moins 8 caractère" ;
        }

        if(!$allow)
            return new JsonResponse(["message"=>$message, "type"=>$type]) ;

        $agence = new Agence() ;
        $agence->setNom(strtoupper($nom));
        $agence->setRegion($region);
        $agence->setCapacite($capacite);
        $agence->setAdresse($adresse) ;
        $agence->setTelephone($telephone) ;
        $agence->setStatut(True) ;
        $agence->setCreatedAt(new \DateTimeImmutable) ;
        $agence->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($agence);
        $this->entityManager->flush();

        $user = new User() ;
        
        $encodedPass = $this->appService->hashPassword($user,$password) ;

        $user->setUsername(strtoupper($username)) ;
        $user->setEmail($email);
        $user->setPassword($encodedPass) ;
        $user->setPoste($poste) ;
        $user->setAgence($agence) ;
        $user->setStatut(True) ; 
        $user->setRoles(["MANAGER"]) ;
        $user->setCreatedAt(new \DateTimeImmutable) ;
        $user->setUpdatedAt(new \DateTimeImmutable) ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(["message"=>$message, "type"=>$type]) ;
    }

    #[Route('/admin/password/get', name:"getRandomPass")]
    public function getRandomPass()
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }

        $randomPass = $this->appService->generatePassword() ;

        return new JsonResponse(["randomPass" => $randomPass]) ;
    }

    #[Route('/admin/societe/list',name:'admin_listSociete')]
    public function listSociete()
    {
        $allowUrl = $this->appService->checkUrl() ;
        if(!$allowUrl)
        {
            $url = $this->generateUrl('app_login');
            return new RedirectResponse($url);
        }

        $agences = $this->entityManager->getRepository(Agence::class)->findAll() ;

        return $this->render('admin/societe/list.html.twig',[
            "agences" => $agences
        ]);
    }
}
